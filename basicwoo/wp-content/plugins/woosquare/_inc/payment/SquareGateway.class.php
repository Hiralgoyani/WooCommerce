<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WooSquare_Gateway extends WC_Payment_Gateway { 
	protected $connect;
	protected $token;
	public $log;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id		            = 'square';
		$this->method_title 	    = __( 'Square', 'woosquare' );
		$this->method_description   = __( 'Square works by adding payments fields in an iframe and then sending the details to Square for verification and processing.', 'woosquare' );
		$this->has_fields 	        = true;
		$this->supports 	        = array(
			'products',
			'refunds',
		);

		// Load the form fields
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values
		$this->title           = $this->get_option( 'title' );
		$this->description     = $this->get_option( 'description' );
		$this->enabled         = $this->get_option( 'enabled' ) === 'yes' ? 'yes' : 'no';
		$this->capture         = $this->get_option( 'capture' ) === 'yes' ? true : false;
		$this->create_customer = $this->get_option( 'create_customer' ) === 'yes' ? true : false;
		$this->logging         = $this->get_option( 'logging' ) === 'yes' ? true : false;
		$this->connect         = new WooSquare_Payments_Connect(); // decouple in future when v2 is ready
		$this->token           = get_option( 'woo_square_access_token_free' );
		
		$woocommerce_square_settings = get_option('woocommerce_square_settings');
		if($woocommerce_square_settings['enable_sandbox'] == 'yes'){
			$this->description .= ' ' . __( 'STAGING MODE IS ENABLED". For testing purpose use card number 4111111111111111 with any CVC and valid expiration date.', 'woosquare' );
			$this->token = $woocommerce_square_settings['sandbox_access_token'];
		}	
		
		$this->description  = trim( $this->description );
		$this->connect->set_access_token( $this->token );

		// Hooks
		// if cart having subscription type product disabled below script else work..
		if(in_array('wc-square-recurring-premium/wc-square-recuring.php', apply_filters('active_plugins', get_option('active_plugins'))) and is_checkout() ) {			
			$sub = false;
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product = wc_get_product( $cart_item['product_id'] );
				if( $_product->is_type( 'subscription' ) OR $_product->is_type( 'variable-subscription' ) ){
					$sub = true;
				}
			}
		}
		if(!@$sub){
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		}		
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * get_icon function.
	 *
	 * @access public
	 * @return string
	 */
	public function get_icon() {
		$icon  = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/visa.svg' ) . '" alt="Visa" width="32" style="margin-left: 0.3em" />';
		$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/mastercard.svg' ) . '" alt="Mastercard" width="32" style="margin-left: 0.3em" />';
		$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/amex.svg' ) . '" alt="Amex" width="32" style="margin-left: 0.3em" />';

		$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/discover.svg' ) . '" alt="Discover" width="32" style="margin-left: 0.3em" />';
		
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}

	/**
	 * Check if required fields are set
	 */
	public function admin_notices() {
		if ( ! $this->enabled ) {
			return;
		}

		// Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected
		if ( ! WC_SQUARE_ENABLE_STAGING && get_option( 'woocommerce_force_ssl_checkout' ) === 'no' && ! class_exists( 'WordPressHTTPS' ) ) {
			echo '<div class="error"><p>' . sprintf( __( 'Square is enabled, but the <a href="%s">force SSL option</a> is disabled; your checkout is not secured! Please enable SSL and ensure your server has a valid SSL certificate.', 'woosquare' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . '</p></div>';
		}
	}

	/**
	 * Check if this gateway is enabled
	 */
	public function is_available() {
		$is_available = true;

		if ( $this->enabled ) {
			if ( ! WC_SQUARE_ENABLE_STAGING && ! wc_checkout_is_https() ) {
				$is_available = false;
			}

			if ( ! WC_SQUARE_ENABLE_STAGING && empty( $this->token ) ) {
				$is_available = true;
			}
			

			// Square only supports US, Canada and Australia for now.
			if ( ( 
				'US' !== WC()->countries->get_base_country() && 
				'CA' !== WC()->countries->get_base_country() && 
				'GB' !== WC()->countries->get_base_country() &&
				'JP' !== WC()->countries->get_base_country() &&
				'AU' !== WC()->countries->get_base_country() ) || ( 
				'USD' !== get_woocommerce_currency() && 
				'CAD' !== get_woocommerce_currency() && 
				'JPY' !== get_woocommerce_currency() && 
				'AUD' !== get_woocommerce_currency() && 
				'GBP' !== get_woocommerce_currency() ) 
				) {
				$is_available = false;
			}
			
		} else {
			$is_available = false;
		}
			
		return apply_filters( 'woocommerce_square_payment_gateway_is_available', $is_available );
	}

	/**
	 * Initialize Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = apply_filters( 'woocommerce_square_gateway_settings', array(
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'woosquare' ),
				'label'       => __( 'Enable Square', 'woosquare' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'title' => array(
				'title'       => __( 'Title', 'woosquare' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woosquare' ),
				'default'     => __( 'Credit card (Square)', 'woosquare' )
			),
			'description' => array(
				'title'       => __( 'Description', 'woosquare' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woosquare' ),
				'default'     => __( 'Pay with your credit card via Square.', 'woosquare')
			),
			'capture' => array(
				'title'       => __( 'Delay Capture', 'woosquare' ),
				'label'       => __( 'Enable Delay Capture', 'woosquare' ),
				'type'        => 'checkbox',
				'description' => __( 'When enabled, the request will only perform an Auth on the provided card. You can then later perform either a Capture or Void.', 'woosquare' ),
				'default'     => 'no'
			),
			'create_customer' => array(
				'title'       => __( 'Create Customer', 'woosquare' ),
				'label'       => __( 'Enable Create Customer', 'woosquare' ),
				'type'        => 'checkbox',
				'description' => __( 'When enabled, processing a payment will create a customer profile on Square.', 'woosquare' ),
				'default'     => 'no'
			),
			'logging' => array(
				'title'       => __( 'Logging', 'woosquare' ),
				'label'       => __( 'Log debug messages', 'woosquare' ),
				'type'        => 'checkbox',
				'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'woosquare' ),
				'default'     => 'no'
			),
			'enable_sandbox' => array(
				'title'       => __( 'Enable/Disable', 'wpexpert-square' ),
				'label'       => __( 'Enable Sandbox', 'wpexpert-square' ),
				'type'        => 'checkbox',
				'description' => __( 'Test your transaction through sandbox mode.', 'wpexpert-square' ),
				'default'     => 'no'
			),
			'api_details'           => array(
				'title'       => __( 'Sandbox API credentials', 'wpexpert-square' ),
				'type'        => 'title',
				/* translators: %s: URL */
				'description' => sprintf( __( '<div class="squ-sandbox-description" style="%s"><p>%s</p></div> <div class="squ-sandbox-description"><p>If you don\'t have an account, go to <a target="_blank" href="%s">https://squareup.com/signup</a> to create one. You need a Square account to register an application with Square. 
													Register your application with Square
												</p>
												<p>
													Then go to <a  target="_blank" href="%s">https://connect.squareup.com/apps</a> and sign in to your Square account. Then <b>click New Application</b> and give the name for your application to Create App.

													The application dashboard displays your new app\'s sandbox credentials. Insert below these sandbox credentials.   
												</p></div>
												', 'wpexpert-square' ), '	padding: 3px 0px 3px 10px;
																			background-color: #0085ba;
																			color: white; 
																			font-size: medium;
																			font-weight: 400;
																			margin-bottom: 15px;'
																		,   'These settings are required only for sandbox!','https://squareup.com/signup','https://connect.squareup.com/apps' ),
			),
			'sandbox_application_id' => array(
				'title'       => __( 'Sandbox application id', 'wpexpert-square' ),
				'label'       => __( '', 'wpexpert-square' ),
				'type'        => 'textbox',
				'description' => __( 'Add Square Application ID settings to integrate with square payment with sandbox.', 'wpexpert-square' ),
				'default'     => ''
			),
			'sandbox_access_token' => array(
				'title'       => __( 'Sandbox access token', 'wpexpert-square' ),
				'label'       => __( '', 'wpexpert-square' ),
				'type'        => 'textbox',
				'description' => __( 'Add Square Access token settings to integrate with square payment with sandbox.', 'wpexpert-square' ),
				'default'     => ''
			),
			'sandbox_location_id' => array(
				'title'       => __( 'Sandbox location id', 'wpexpert-square' ),
				'label'       => __( 'Enable Sandbox', 'wpexpert-square' ),
				'type'        => 'textbox',
				'description' => __( 'Add Square Location ID settings to integrate with square payment with sandbox.', 'wpexpert-square' ),
				'default'     => ''
			),
		) );
	}

	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() { ?>
		<fieldset class="wooSquare-checkout">
			<?php
				$allowed = array(
				    'a' => array(
				        'href' => array(),
				        'title' => array()
				    ),
				    'br' => array(),
				    'em' => array(),
				    'strong' => array(),
				    'span'	=> array(
				    	'class' => array(),
				    ),
				);
				if ( $this->description ) {
					echo apply_filters( 'woocommerce_square_description', wpautop( wp_kses( $this->description, $allowed ) ) );
				}
			?>
			<p class="form-row form-row-wide">
				<label for="sq-card-number"><?php esc_html_e( 'Card Number', 'woosquare' ); ?> <span class="required">*</span></label>
				<input id="sq-card-number" type="text" maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••" name="<?php echo esc_attr( $this->id ); ?>-card-number" />
			</p>
			
			<p class="form-row form-row-first">
				<label for="sq-expiration-date"><?php esc_html_e( 'Expiry (MM/YY)', 'woosquare' ); ?> <span class="required">*</span></label>
				<input id="sq-expiration-date" type="text" autocomplete="off" placeholder="<?php esc_attr_e( 'MM / YY', 'woosquare' ); ?>" name="<?php echo esc_attr( $this->id ); ?>-card-expiry" />
			</p>

			<p class="form-row form-row-last">
				<label for="sq-cvv"><?php esc_html_e( 'Card Code', 'woosquare' ); ?> <span class="required">*</span></label>
				<input id="sq-cvv" type="text" autocomplete="off" placeholder="<?php esc_attr_e( 'CVV', 'woosquare' ); ?>" name="<?php echo esc_attr( $this->id ); ?>-card-cvv" />
			</p>

			<p class="form-row form-row-wide">
				<label for="sq-postal-code"><?php esc_html_e( 'Card Postal Code', 'woosquare' ); ?> <span class="required">*</span></label>
				<input id="sq-postal-code" type="text" autocomplete="off" placeholder="<?php esc_attr_e( 'Card Postal Code', 'woosquare' ); ?>" name="<?php echo esc_attr( $this->id ); ?>-card-postal-code" />
			</p>
		</fieldset>
			
		<?php
	}

	/**
	 * Get payment form input styles.
	 * This function is pass to the JS script in order to style the
	 * input fields within the iFrame.
	 *
	 * Possible styles are: mediaMinWidth, mediaMaxWidth, backgroundColor, boxShadow,
	 * color, fontFamily, fontSize, fontWeight, lineHeight and padding.
	 *
	 * @since 1.0.4
	 * @version 1.0.4
	 * @access public
	 * @return json $styles
	 */
	public function get_input_styles() {
		$styles = array(
			array(
				'fontSize'        => '1.2em',
				'padding'         => '.618em',
				'fontWeight'      => 400,
				'backgroundColor' => 'transparent',
				'lineHeight'      => 1.7
			),
			array(
				'mediaMaxWidth' => '1200px',
				'fontSize'      => '1em'
			)
		);

		return apply_filters( 'woocommerce_square_payment_input_styles', wp_json_encode( $styles ) );
	}

	/**
	 * payment_scripts function.
	 *
	 *
	 * @access public
	 */
	public function payment_scripts() {
		if ( ! is_checkout() ) {
			return;
		}

		// $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'square', 'https://js.'.WC_SQUARE_STAGING_URL.'.com/v2/paymentform', '', '0.0.2', true );
		wp_register_script( 'woocommerce-square', WooSquare_PLUGIN_URL . '/_inc/js/SquarePayments.js', array( 'jquery', 'square' ), WooSquare_VERSION, true );
		if(WC_SQUARE_ENABLE_STAGING){ 
			 $env=  'production'; 
		} else {
			 $env=  'staging'; 
		}
		
		global $woocommerce;
		// Will get you cart object
		$cart_total = $woocommerce->cart->get_totals();
		$woocommerce_square_settings = get_option('woocommerce_square_settings');
		
		$location = get_option('woo_square_location_id');
		if($woocommerce_square_settings['enable_sandbox'] == 'yes'){
			$location = $woocommerce_square_settings['sandbox_location_id'];
		}
		wp_localize_script( 'woocommerce-square', 'square_params', array(
			'application_id'               => SQUARE_APPLICATION_ID,
			'environment'                  =>  'production' ,
			'locationId'                   =>  $location,
			'cart_total'                   =>  $cart_total['total'] ,
			'get_woocommerce_currency'	   =>  get_woocommerce_currency(),
			'placeholder_card_number'      => __( '•••• •••• •••• ••••', 'woosquare' ),
			'placeholder_card_expiration'  => __( 'MM / YY', 'woosquare' ),
			'placeholder_card_cvv'         => __( 'CVV', 'woosquare' ),
			'placeholder_card_postal_code' => __( 'Card Postal Code', 'woosquare' ),
			'payment_form_input_styles'    => esc_js( $this->get_input_styles() ),
			'custom_form_trigger_element'  => apply_filters( 'woocommerce_square_payment_form_trigger_element', esc_js( '' ) ),
		) );

		wp_enqueue_script( 'woocommerce-square' );
		
		wp_enqueue_style( 'woocommerce-square-styles', WooSquare_PLUGIN_URL . '/_inc/css/SquareFrontendStyles.css' );

		return true;
	}

	/**
	 * Process the payment
	 */
	public function process_payment( $order_id, $retry = true ) {
		$order    = wc_get_order( $order_id );
		$nonce    = isset( $_POST['square_nonce'] ) ? wc_clean( $_POST['square_nonce'] ) : '';
		$buyerVerification_token    = isset( $_POST['buyerVerification_token'] ) ? wc_clean( $_POST['buyerVerification_token'] ) : '';
		$currency = $order->get_order_currency();
		
		$this->log( "Info: Begin processing payment for order {$order_id} for the amount of {$order->get_total()}" );
		$woocommerce_square_settings = get_option('woocommerce_square_settings');					
		
		try {
			$uid = uniqid();
			
			if(function_exists('square_order_sync_add_on')){
				$amount = (int) round($this->format_amount( $order->get_total(), $currency ),1);
			} else {
				$amount = (int) $this->format_amount( $order->get_total(), $currency );
			}
			
			/* $data = array(
				'idempotency_key' => $uid,
				'amount_money'    => array( 
					'amount'   => $amount,
					'currency' => $currency,
				),
				'reference_id'        => (string) $order->get_order_number(),
				'delay_capture'       => $this->capture ? true : false,
				'card_nonce'          => $nonce,
				'buyer_email_address' => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_email : $order->get_billing_email(),
				'billing_address'     => array(
					'address_line_1'                  => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_address_1 : $order->get_billing_address_1(),
					'address_line_2'                  => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_address_2 : $order->get_billing_address_2(),
					'locality'                        => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_city : $order->get_billing_city(),
					'administrative_district_level_1' => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_state : $order->get_billing_state(),
					'postal_code'                     => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_postcode : $order->get_billing_postcode(),
					'country'                         => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_country : $order->get_billing_country(),
				),
				'note' => apply_filters( 'woosquare_payment_order_note', 'WooCommerce: Order #' . (string) $order->get_order_number(), $order ),
			); 

			if ( $order->needs_shipping_address() ) {
				$data['shipping_address'] = array(
					'address_line_1'                  => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->shipping_address_1 : $order->get_shipping_address_1(),
					'address_line_2'                  => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->shipping_address_2 : $order->get_shipping_address_2(),
					'locality'                        => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->shipping_city : $order->get_shipping_city(),
					'administrative_district_level_1' => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->shipping_state : $order->get_shipping_state(),
					'postal_code'                     => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->shipping_postcode : $order->get_shipping_postcode(),
					'country'                         => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->shipping_country : $order->get_shipping_country(),
				);
			}
			
			*/
			$woo_square_locations = get_option('woo_square_location_id_free');
			$woocommerce_square_settings = get_option('woocommerce_square_settings');
			if($woocommerce_square_settings['enable_sandbox'] == 'yes'){
				$woo_square_locations = $woocommerce_square_settings['sandbox_location_id'];
			}
			
			
			$fields = array(
				"idempotency_key" => $uid,
				"location_id" => $woo_square_locations,
				"autocomplete" => $this->capture ? false : true,
				"amount_money" => array(
					  "amount" => $amount,
					  "currency" => $currency
					),
				"source_id" => $nonce,
				"verification_token" => $buyerVerification_token,
				'note' => apply_filters( 'woosquare_payment_order_note', 'WooCommerce: Order #' . (string) $order->get_order_number(), $order ),
			);
			
			
		

			//need to add order creation function and get the order id.
			if(function_exists('square_order_sync_add_on')){
				$data['order_id'] = square_order_sync_add_on($order,$woo_square_locations,$currency,$uid);
			}	
			$url = "https://connect.".WC_SQUARE_STAGING_URL.".com/v2/payments";
			$headers = array(
				'Accept' => 'application/json',
				'Authorization' => 'Bearer '.$this->token,
				'Content-Type' => 'application/json',
				'Cache-Control' => 'no-cache'
			);
			
		
			$result = json_decode(wp_remote_retrieve_body(wp_remote_post($url, array(
					'method' => 'POST',
					'headers' => $headers,
					'httpversion' => '1.0',
					'sslverify' => false,
					'body' => json_encode($fields)
					)
				)
			)
			);
			// $result = $this->connect->charge_card_nonce( $woo_square_locations, $data );
			
			if ( is_wp_error( $result ) ) {
				wc_add_notice( __( 'Error: Unable to complete your transaction with square due to some issue. For now you can try some other payment method or try again later.', 'woosquare' ), 'error' );

				throw new Exception( $result->get_error_message() );
			}

			if ( ! empty( $result->errors ) ) {
				if ( 'INVALID_REQUEST_ERROR' === $result->errors[0]->category ) {
					wc_add_notice( __( 'Error: Unable to complete your transaction with square due to some issue. For now you can try some other payment method or try again later.', 'woosquare' ), 'error' );
				}
				
				if ( 'PAYMENT_METHOD_ERROR' === $result->errors[0]->category || 'VALIDATION_ERROR' === $result->errors[0]->category ) {
					// format errors for display
					$error_html = __( 'Payment Error: ', 'woosquare' );
					$error_html .= '<br />';
					$error_html .= '<ul>';

					foreach( $result->errors as $error ) {
						$error_html .= '<li>' . $error->detail . '</li>';
					}

					$error_html .= '</ul>';

					wc_add_notice( $error_html, 'error' );
				}
				
				$errors = print_r( $result->errors, true );

				throw new Exception( $errors );
			}

			if ( empty( $result ) ) {
				wc_add_notice( __( 'Error: Unable to complete your transaction with square due to some issue. For now you can try some other payment method or try again later.', 'woosquare' ), 'error' );

				throw new Exception( 'Unknown Error' );
			}

			if ( 'CAPTURED' === $result->payment->card_details->status ) {
				// Store captured value
				update_post_meta( $order->id, '_square_charge_captured', 'yes' );

				// Payment complete
				$order->payment_complete( $result->payment->id );
				
				// Add order note
				//update square transaction id for refund woocommerce squareup payment order  
				add_post_meta( $order->id, 'woosquare_transaction_id', $result->payment->id, true );
				add_post_meta( $order->id, 'square_payment_id', $result->payment->id, true );
				

				//check order by sandbox/live
				if($woocommerce_square_settings['enable_sandbox'] == 'yes'){
					$msg = ' via Sandbox ';
				} else {
					$msg = '';
				}
				
				$complete_message = sprintf( __( 'Square charge complete '.$msg.' (Charge ID: %s)', 'woosquare' ), $result->payment->id );
				$order->add_order_note( $complete_message );
				$this->log( "Success: $complete_message" );				
			} else {
				// Store captured value
				update_post_meta( $order->id, '_square_charge_captured', 'no' );
				add_post_meta( $order->id, 'woosquare_transaction_id', $result->payment->id, true );
				add_post_meta( $order->id, 'square_payment_id', $result->payment->id, true );

				// Mark as on-hold
				$authorized_message = sprintf( __( 'Square charge authorized (Authorized ID: %s). Process order to take payment, or cancel to remove the pre-authorization.', 'woosquare' ), $result->payment->id );
				$order->update_status( 'on-hold', $authorized_message );
				$this->log( "Success: $authorized_message" );

				// Reduce stock levels
				version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->reduce_order_stock() : wc_reduce_stock_levels( $order_id );
			}

			// we got this far which means the transaction went through
			if ( $this->create_customer ) {
				$this->maybe_create_customer( $order );
			}
			
			// Remove cart
			WC()->cart->empty_cart();

			// Return thank you page redirect
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order )
			);
		} catch ( Exception $e ) {
			$this->log( sprintf( __( 'Error: %s', 'woosquare' ), $e->getMessage() ) );

			$order->update_status( 'failed', $e->getMessage() );

			return;
		}
	}

	/**
	 * Tries to create the customer on Square
	 *
	 * @param object $order
	 */
	public function maybe_create_customer( $order ) {
		$user               = get_current_user_id();
		$square_customer_id = get_user_meta( $user, '_square_customer_id', true );

		$create_customer = true;

		$customer = array(
			'given_name'    => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_first_name : $order->get_billing_first_name(),
			'family_name'   => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_last_name : $order->get_billing_last_name(),
			'email_address' => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_email : $order->get_billing_email(),
			'address'       => array(
				'address_line_1'                  => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_address_1 : $order->get_billing_address_1(),
				'address_line_2'                  => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_address_2 : $order->get_billing_address_2(),
				'locality'                        => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_city : $order->get_billing_city(),
				'administrative_district_level_1' => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_state : $order->get_billing_state(),
				'postal_code'                     => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_postcode : $order->get_billing_postcode(),
				'country'                         => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_country : $order->get_billing_country(),
			),
			'phone_number' => (string) version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_phone : $order->get_billing_phone(),
			'reference_id' => ! empty( $user ) ? (string) $user : __( 'Guest', 'woosquare' ),
		);

		// to prevent creating duplicate customer
		// check to make sure this customer does not exist on Square
		if ( ! empty( $square_customer_id ) ) {
			$square_customer = $this->connect->get_customer( $square_customer_id );

			if ( empty( $square_customer->errors ) ) {
				// customer already exist on Square
				$create_customer = false;
			}
		}

		if ( $create_customer ) {
			$result = $this->connect->create_customer( $customer );

			// we don't want to halt any processes here just log it
			if ( is_wp_error( $result ) ) {
				$this->log( sprintf( __( 'Error creating customer: %s', 'woosquare' ), $result->get_error_message() ) );
				$order->add_order_note( sprintf( __( 'Error creating customer: %s', 'woosquare' ), $result->get_error_message() ) );
			}

			// we don't want to halt any processes here just log it
			if ( ! empty( $result->errors ) ) {
				$this->log( sprintf( __( 'Error creating customer: %s', 'woosquare' ), print_r( $result->errors, true ) ) );
				$order->add_order_note( sprintf( __( 'Error creating customer: %s', 'woosquare' ), print_r( $result->errors, true ) ) );
			}

			// if no errors save Square customer ID to user meta
			if ( ! is_wp_error( $result ) && empty( $result->errors ) && ! empty( $user ) ) {
				update_user_meta( $user, '_square_customer_id', $result->customer->id );
				$order->add_order_note( sprintf( __( 'Customer created on Square: %s', 'woosquare' ), $result->customer->id ) );
			}
		}
	}

	/**
	 * Process amount to be passed to Square.
	 * @return float
	 */
	public function format_amount( $total, $currency = '' ) {
		if ( ! $currency ) {
			$currency = get_woocommerce_currency();
		}

		switch ( strtoupper( $currency ) ) {
			// Zero decimal currencies
			case 'BIF' :
			case 'CLP' :
			case 'DJF' :
			case 'GNF' :
			case 'JPY' :
			case 'KMF' :
			case 'KRW' :
			case 'MGA' :
			case 'PYG' :
			case 'RWF' :
			case 'VND' :
			case 'VUV' :
			case 'XAF' :
			case 'XOF' :
			case 'XPF' :
				$total = absint( $total );
				break;
			default :
				$total = round( $total, 2 ) * 100; // In cents
				break;
		}

		return $total;
	}

	/**
	 * Refund a charge
	 * @param  int $order_id
	 * @param  float $amount
	 * @return bool
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );
		
		if ( ! $order || ! get_post_meta( $order_id, 'woosquare_transaction_id', true ) ) {
			return false;
		}

		if ( 'square' === ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->payment_method : $order->get_payment_method() ) ) {
			try {
				$this->log( "Info: Begin refund for order {$order_id} for the amount of {$amount}" );

				$trans_id = get_post_meta( $order_id, 'woosquare_transaction_id', true );
				// $captured = get_post_meta( $order_id, '_square_charge_captured', true );
				$location = get_option('woo_square_location_id');
				
				$woocommerce_square_settings = get_option('woocommerce_square_settings');
				if($woocommerce_square_settings['enable_sandbox'] == 'yes'){
					$location = $woocommerce_square_settings['sandbox_location_id'];
					$token = $woocommerce_square_settings['sandbox_access_token'];
				}
				$this->connect->set_access_token( $token );
				$transaction_status = $this->connect->get_transaction_status( $location, $trans_id );
				
				if ( 'CAPTURED' === $transaction_status ) {
					// $tender_id = $this->connect->get_tender_id( $location, $trans_id );

					// $body = array();

					// $body['idempotency_key'] = uniqid();
					// $body['tender_id']       = $tender_id;

					// if ( ! is_null( $amount ) ) {
						// $body['amount_money'] = array(
							// 'amount'   => $this->format_amount( $amount ),
							// 'currency' => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->get_order_currency() : $order->get_currency(),
						// );
					// }

					// if ( $reason ) {
						// $body['reason'] = $reason;
					// } else {
						// $body['reason'] = '-';
					// }
					
					$fields = array(
						"idempotency_key" => uniqid(),
						"payment_id" => $trans_id,
						"reason" => $reason,
						"amount_money" => array(
							  "amount" => $this->format_amount( $amount ),
							  "currency" => version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->get_order_currency() : $order->get_currency(),
							),
					);
					
					$url = "https://connect.".WC_SQUARE_STAGING_URL.".com/v2/refunds";
					$headers = array(
						'Accept' => 'application/json',
						'Authorization' => 'Bearer '.$this->token,
						'Content-Type' => 'application/json',
						'Cache-Control' => 'no-cache'
					);
					
					$result = json_decode(wp_remote_retrieve_body(wp_remote_post($url, array(
							'method' => 'POST',
							'headers' => $headers,
							'httpversion' => '1.0',
							'sslverify' => false,
							'body' => json_encode($fields)
							)
						)
					)
					);
					
					// $result = $this->connect->refund_transaction( $location, $trans_id, $body );

					if ( is_wp_error( $result ) ) {
						throw new Exception( $result->get_error_message() );

					} elseif ( ! empty( $result->errors ) ) {
						throw new Exception( "Error: " . print_r( $result->errors, true ) );
						
					} else {
						if ( 'APPROVED' === $result->refund->status || 'PENDING' === $result->refund->status ) {
							$refund_message = sprintf( __( 'Refunded %s - Refund ID: %s - Reason: %s', 'woosquare' ), wc_price( $result->refund->amount_money->amount / 100 ), $result->refund->id, $reason );
						
							$order->add_order_note( $refund_message );
						
							$this->log( "Success: " . html_entity_decode( strip_tags( $refund_message ) ) );
						
							return true;
						}
					}
				}

			} catch ( Exception $e ) {
				$this->log( sprintf( __( 'Error: %s', 'woosquare' ), $e->getMessage() ) );

				return false;
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
