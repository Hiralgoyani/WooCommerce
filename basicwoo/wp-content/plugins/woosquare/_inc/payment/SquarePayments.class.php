<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once( dirname( __FILE__ ) . '/SquarePaymentsConnect.class.php' ); 

class WooSquare_Payments {
	protected $connect;
	public $logging;

	/**
	 * Constructor
	 */
	public function __construct( WooSquare_Payments_Connect $connect ) {
		$this->init();
		$this->connect = $connect;

		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );

		add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'capture_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'capture_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_cancelled', array( $this, 'cancel_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_refunded', array( $this, 'cancel_payment' ) );

		if ( is_admin() ) {
			add_filter( 'woocommerce_order_actions', array( $this, 'add_capture_charge_order_action' ) );
			add_action( 'woocommerce_order_action_square_capture_charge', array( $this, 'maybe_capture_charge' ) );
		}

		$gateway_settings = get_option( 'woocommerce_square_settings' );

		$this->logging = ! empty( $gateway_settings['logging'] ) ? true : false;

		return true;
	}

	/**
	 * Init
	 */
	public function init() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}


		// live/production app id from Square account
		
		// $tokenn = get_option('woo_square_access_token_free'); 
		// $tokenn_explode = explode('-',$tokenn);
		
		$woocommerce_square_settings = get_option('woocommerce_square_settings');
		
		if($woocommerce_square_settings['enable_sandbox'] != 'yes'){
			if (!defined('SQUARE_APPLICATION_ID')) define('SQUARE_APPLICATION_ID','sq0idp-OkzqrnM_vuWKYJUvDnwT-g');
			if (!defined('WC_SQUARE_ENABLE_STAGING')) define('WC_SQUARE_ENABLE_STAGING',false);
		} else {
		
			if (!defined('SQUARE_APPLICATION_ID')) define('SQUARE_APPLICATION_ID',$woocommerce_square_settings['sandbox_application_id']);
			if (!defined('WC_SQUARE_ENABLE_STAGING')) define('WC_SQUARE_ENABLE_STAGING',true);
		}
		
		// Includes
		include_once( dirname( __FILE__ ) . '/SquareGateway.class.php' );

		return true;
	}

	/**
	 * Register the gateway for use
	 */
	public function register_gateway( $methods ) {
		$methods[] = 'WooSquare_Gateway';

		return $methods;
	}

	public function add_capture_charge_order_action( $actions ) {
		if ( ! isset( $_REQUEST['post'] ) ) {
			return $actions;
		}

		$order = wc_get_order( $_REQUEST['post'] );

		// bail if the order wasn't paid for with this gateway
		if ( 'square' !== ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->payment_method : $order->get_payment_method() ) ) {
			return $actions;
		}

		// bail if charge was already captured
		if ( 'yes' === get_post_meta( version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->id : $order->get_id(), '_square_charge_captured', true ) ) {
			return $actions;
		}

		$actions['square_capture_charge'] = esc_html__( 'Capture Charge', 'woosquare' );

		return $actions;
	}

	public function maybe_capture_charge( $order ) {
		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

		$this->capture_payment( version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->id : $order->get_id() );

		return true;
	}

	/**
	 * Capture payment when the order is changed from on-hold to complete or processing
	 *
	 * @param int $order_id
	 */
	public function capture_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( 'square' === ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->payment_method : $order->get_payment_method() ) ) {
			try {
				$this->log( "Info: Begin capture for order {$order_id}" );

				$trans_id = get_post_meta( $order_id, 'woosquare_transaction_id', true );
				$captured = get_post_meta( $order_id, '_square_charge_captured', true );
				$location = get_option('woo_square_location_id');
				$token    = get_option( 'woocommerce_square_merchant_access_token' );
				
				$woocommerce_square_settings = get_option('woocommerce_square_settings');
				if($woocommerce_square_settings['enable_sandbox'] == 'yes'){
					$location = $woocommerce_square_settings['sandbox_location_id'];
					$token    = $woocommerce_square_settings['sandbox_access_token'];
				}
				$this->connect->set_access_token( $token );

				$transaction_status = $this->connect->get_transaction_status( $location, $trans_id );
				
				if ( 'AUTHORIZED' === $transaction_status ) {
					
					$url = "https://connect.".WC_SQUARE_STAGING_URL.".com/v2/payments/$trans_id/complete";
					$headers = array(
						'Accept' => 'application/json',
						'Authorization' => 'Bearer '.$token,
						'Content-Type' => 'application/json',
						'Cache-Control' => 'no-cache'
					);
					
					
					$result = json_decode(wp_remote_retrieve_body(wp_remote_post($url, array(
							'method' => 'POST',
							'headers' => $headers,
							'httpversion' => '1.0',
							'sslverify' => false,
							'body' => ""
							)
						)
					)
					);
					
					
					// $result = $this->connect->capture_transaction( $location, $trans_id ); // returns empty object
					
					if ( is_wp_error( $result ) ) {
						$order->add_order_note( __( 'Unable to capture charge!', 'woosquare' ) . ' ' . $result->get_error_message() );

						throw new Exception( $result->get_error_message() );
					} elseif ( ! empty( $result->errors ) ) {
						$order->add_order_note( __( 'Unable to capture charge!', 'woosquare' ) . ' ' . print_r( $result->errors, true ) );

						throw new Exception( print_r( $result->errors, true ) );
					} else {
						$tokenn = get_option('woo_square_access_token');
						$tokenn_explode = explode('-',$tokenn);
						$sandorlive =  $tokenn_explode[0];
				
						if($sandorlive == 'sandbox'){
							$msg = ' via Sandbox ';
						} else {
							$msg = '';
						}
						
						$order->add_order_note( sprintf( __( 'Square charge complete '.$msg.' (Charge ID: %s)', 'woosquare' ), $trans_id ) );
						update_post_meta( $order->id, '_square_charge_captured', 'yes' );
						$this->log( "Info: Capture successful for {$order_id}" );
					}
				}
			} catch ( Exception $e ) {
				$this->log( sprintf( __( 'Error unable to capture charge: %s', 'woosquare' ), $e->getMessage() ) );
			}
		}
	}

	/**
	 * Cancel authorization
	 *
	 * @param  int $order_id
	 */
	public function cancel_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( 'square' === ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->payment_method : $order->get_payment_method() ) ) {
			try {
				$this->log( "Info: Cancel payment for order {$order_id}" );

				$trans_id = get_post_meta( $order_id, 'woosquare_transaction_id', true );
				
				$location = get_option('woo_square_location_id');
				$token    = get_option( 'woocommerce_square_merchant_access_token' );
				$woocommerce_square_settings = get_option('woocommerce_square_settings');
				if(@$woocommerce_square_settings['enable_sandbox'] == 'yes'){
					$location = $woocommerce_square_settings['sandbox_location_id'];
					$token    = $woocommerce_square_settings['sandbox_access_token'];
				}
				

				$this->connect->set_access_token( $token );
				
				$transaction_status = $this->connect->get_transaction_status( $location, $trans_id );
				
				if ( 'AUTHORIZED' === $transaction_status ) {
					// $result = $this->connect->void_transaction( $location, $trans_id ); // returns empty object
					
					
						$url = "https://connect.".WC_SQUARE_STAGING_URL.".com/v2/payments/$trans_id/cancel";
						$headers = array(
							'Accept' => 'application/json',
							'Authorization' => 'Bearer '.$token,
							'Content-Type' => 'application/json',
							'Cache-Control' => 'no-cache'
						);
						
						
						$result = json_decode(wp_remote_retrieve_body(wp_remote_post($url, array(
								'method' => 'POST',
								'headers' => $headers,
								'httpversion' => '1.0',
								'sslverify' => false,
								'body' => ""
								)
							)
						)
						);
					
					
					// $result = $this->connect->void_transaction( $location, $trans_id ); // returns empty object
					$transaction_status = $this->connect->get_transaction_status( $location, $trans_id );
					
					if ( is_wp_error( $result ) ) {
						$order->add_order_note( __( 'Unable to void charge!', 'woosquare' ) . ' ' . $result->get_error_message() );
						throw new Exception( $result->get_error_message() );
					} elseif ( ! empty( $result->errors ) ) {
						$order->add_order_note( __( 'Unable to void charge!', 'woosquare' ) . ' ' . print_r( $result->errors, true ) );

						throw new Exception( print_r( $result->errors, true ) );
					} else if ( 'VOIDED' === $transaction_status )  {
						$order->add_order_note( sprintf( __( 'Square charge voided! (Charge ID: %s)', 'woosquare' ), $trans_id ) );
						delete_post_meta( $order_id, '_square_charge_captured' );
						delete_post_meta( $order_id, 'woosquare_transaction_id' );
					}
				}
			} catch ( Exception $e ) {
				$this->log( sprintf( __( 'Unable to void charge!: %s', 'woosquare' ), $e->getMessage() ) );
			}
		}
	}

	/**
	 * Logs
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $message
	 */
	public function log( $message ) {
		if ( $this->logging ) {
			WooSquare_Payment_Logger::log( $message );
		}
	}
}

new WooSquare_Payments( new WooSquare_Payments_Connect() );
