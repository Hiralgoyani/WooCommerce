<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class WooSquare_Payments_Connect extends WooSquare_Client {
	const LOCATIONS_CACHE_KEY = 'WooSquare_payments_locations';

	/**
	 * @var string
	 */
	protected $api_version = 'v2';

	/**
	 * Checks to see if token is valid. 
	 *
	 * There is no formal way to check this other than to
	 * retrieve the merchant account details and if it comes back
	 * with a code 200, we assume it is valid.
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function is_valid_token() {

		$merchant = $this->request( 'Retrieving Merchant', 'locations' );

		if ( is_wp_error( $merchant ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Charges the card nonce.
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param string $location_id
	 * @param array $data
	 * @return object
	 */
	public function charge_card_nonce( $location_id, $data ) {
		$path = '/locations/' . $location_id . '/transactions';
		return $this->request( 'Charge Card Nonce', $path, 'POST', $data );
	}

	/**
	 * Retrieves a transaction from Square
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param string $location_id
	 * @param string $transaction_id
	 * @return object
	 */
	public function get_transaction( $location_id, $transaction_id ) {
		$path = '/payments/' . $transaction_id;
		
		return $this->request( 'Get Transaction', $path );
	}

	/**
	 * Gets the transaction status
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param string $location_id
	 * @param string $transaction_id
	 * @return object
	 */
	public function get_transaction_status( $location_id, $transaction_id ) {
		$result = $this->get_transaction( $location_id, $transaction_id );

		if ( is_wp_error( $result ) ) {
			return null;
		}

		return $result->payment->card_details->status;
	}

	/**
	 * Gets the tender id of the transaction
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param string $location_id
	 * @param string $transaction_id
	 * @return object
	 */
	public function get_tender_id( $location_id, $transaction_id ) {
		$result = $this->get_transaction( $location_id, $transaction_id );

		if ( is_wp_error( $result ) ) {
			return null;
		}

		return $result->transaction->tenders[0]->id;
	}

	/**
	 * Capture a previously authorized transaction ( delay/capture )
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param string $location_id
	 * @param string $transaction_id
	 * @return object
	 */
	public function capture_transaction( $location_id, $transaction_id ) {
		$path = '/locations/' . $location_id . '/transactions/' . $transaction_id . '/capture';

		return $this->request( 'Capture Transaction', $path, 'POST' );
	}

	/**
	 * Voids a previously authorized transaction ( delay/capture )
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param string $location_id
	 * @param string $transaction_id
	 * @return object
	 */
	public function void_transaction( $location_id, $transaction_id ) {
		$path = '/locations/' . $location_id . '/transactions/' . $transaction_id . '/void';

		return $this->request( 'Void Authorized Transaction', $path, 'POST' );
	}

	/**
	 * Refunds a transaction
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param string $location_id
	 * @param string $transaction_id
	 * @param array $data
	 * @return object
	 */
	public function refund_transaction( $location_id, $transaction_id, $data ) {
		$path = '/locations/' . $location_id . '/transactions/' . $transaction_id . '/refund';

		return $this->request( 'Refund Transaction', $path, 'POST', $data );
	}

	/**
	 * Create a customer
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param array $data
	 * @return object
	 */
	public function create_customer( $data ) {
		$path = '/customers';

		return $this->request( 'Create Customer', $path, 'POST', $data );
	}

	/**
	 * Get a customer
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param string $customer_id
	 * @return object
	 */
	public function get_customer( $customer_id = null ) {
		if ( null === $customer_id ) {
			return false;
		}

		$path = '/customers/' . $customer_id;

		return $this->request( 'Get Customer', $path, 'GET' );
	}

	/**
	 * @param $path
	 *
	 * @return string
	 */
	protected function get_request_url( $path ) {
		$api_url_base = trailingslashit( $this->get_api_url() );

		$request_path = ltrim( $path, '/' );
		$request_url  = untrailingslashit( $api_url_base . $request_path );

		return $request_url;
	}
}
