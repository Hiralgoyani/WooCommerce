<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
 
/**
 * Class WooSquare_Client
 *
 * Makes actual HTTP requests to the Square API.
 * Handles:
 * - Authentication
 * - Endpoint selection (API version, Merchant ID in path)
 * - Request retries
 * - Paginated results
 * - Content-Type negotiation (JSON)
 */
class WooSquare_Client {

	/**
	 * @var
	 */
	protected $access_token;

	/**
	 * @var
	 */
	protected $merchant_id;

	/**
	 * @var string
	 */
	protected $api_version = 'v1';

	/**
	 * @return mixed
	 */
	public function get_access_token() {

		return $this->access_token;

	}

	/**
	 * @param $token
	 */
	public function set_access_token( $token ) {

		$this->access_token = $token;

	}

	/**
	 * @return mixed
	 */
	public function get_merchant_id() {

		return $this->merchant_id;

	}

	/**
	 * @param $merchant_id
	 */
	public function set_merchant_id( $merchant_id ) {

		$this->merchant_id = $merchant_id;

	}

	/**
	 * @return string
	 */
	public function get_api_version() {

		return $this->api_version;

	}

	/**
	 * @param $version
	 */
	public function set_api_version( $version ) {

		$this->api_version = $version;

	}

	/**
	 * @return int|mixed|void
	 */
	public function get_api_url_base() {
		return apply_filters( 'woocommerce_square_api_url', 'https://connect.'.WC_SQUARE_STAGING_URL.'.com/' );
	}

	/**
	 * @return string
	 */
	public function get_api_url() {

		$url  = trailingslashit( $this->get_api_url_base() );
		$url .= trailingslashit( $this->get_api_version() );

		return $url;

	}

	/**
	 * @return int|mixed|void
	 */
	public function get_request_args() {

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . sanitize_text_field( $this->get_access_token() ),
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
			),
			'user-agent'  => 'WooCommerceSquare/' . WooSquare_VERSION . '; ' . get_bloginfo( 'url' ),
			'timeout'     => 45,
			'httpversion' => '1.1',
		);

		return apply_filters( 'woocommerce_square_request_args', $args );
	}

	/**
	 * @param $path
	 *
	 * @return string
	 */
	protected function get_request_url( $path ) {

		$api_url_base = trailingslashit( $this->get_api_url() );
		$merchant_id  = '';

		// Add merchant ID to the request URL if we aren't hitting /me/*
		if ( strpos( trim( $path, '/' ), 'me' ) !== 0 ) {

			$merchant_id = trailingslashit( $this->get_merchant_id() );

		}

		$request_path = ltrim( $path, '/' );
		$request_url  = trailingslashit( $api_url_base . $merchant_id . $request_path );

		return $request_url;

	}

	/**
	 * Gets the number of retries per request
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param int $count
	 * @return int
	 */
	public function request_retries( $count = 5 ) {

		return apply_filters( 'woocommerce_square_request_retries', $count );

	}

	/**
	 * Wrapper around http_request() that handles pagination for List endpoints.
	 *
	 * @param string $debug_label Description of the request, for logging.
	 * @param string $path        API endpoint path to hit. E.g. /items/
	 * @param string $method      HTTP method to use. Defaults to 'GET'.
	 * @param mixed  $body        Optional. Request payload - will be JSON encoded if non-scalar.
	 *
	 * @return bool|object|WP_Error
	 */
	public function request( $debug_label, $path, $method = 'GET', $body = null ) {
		// we need to check for cURL
		if ( ! function_exists( 'curl_init' ) ) {
			WooSquare_Sync_Logger::log( 'cURL is not available. Sync aborted. Please contact your host to install cURL.' );

			return false;
		}

		// The access token is required for all requests
		$access_token = $this->get_access_token();

		if ( empty( $access_token ) ) {

			return false;

		}

		$request_url = $this->get_request_url( $path );
		$return_data = array();

		while ( true ) {

			$parsed_response = $this->http_request( $debug_label, $request_url, $method, $body );

			if ( ! $parsed_response ) {

				return $parsed_response;

			}

			// A paged list result will be an array, so let's merge if we're already returning an array
			if ( ( 'GET' === $method ) && is_array( $return_data ) && is_array( $parsed_response['decoded_body'] ) ) {

				$return_data = array_merge( $return_data, $parsed_response['decoded_body'] );

			} else {

				$return_data = $parsed_response['decoded_body'];

			}

			// Look for the next page, if specified
			if ( ! preg_match( '/Link/', $parsed_response['headers'] ) ) {
				return $return_data;
			}

			$rel_link_matches = array();

			// Set up the next page URL for the following loop
			if ( ( 'GET' === $method ) && preg_match( "|^<(.+)>;rel='next'$|", $parsed_response['headers'], $rel_link_matches ) ) {

				$request_url = $rel_link_matches[1];
				$body        = null;

			} else {

				return $return_data;

			}

		}

	}

	/**
	 * Helper method to make HTTP requests to the Square API, with retries.
	 *
	 * @param string $debug_label Description of the request, for logging.
	 * @param string $request_url URL to request.
	 * @param string $method      HTTP method to use. Defaults to 'GET'.
	 * @param mixed  $body        Optional. Request payload - will be JSON encoded if non-scalar.
	 *
	 * @return bool|object|WP_Error
	 */
	private function http_request( $debug_label, $request_url, $method = 'GET', $body = null ) {

		$request_args = $this->get_request_args();

		if ( ! is_null( $body ) ) {

			if ( ! empty( $request_args['headers']['Content-Type'] ) && ( 'application/json' === $request_args['headers']['Content-Type'] ) ) {

				$request_args['body'] = json_encode( $body );

			} else {

				$request_args['body'] = $body;

			}

		}

		// Make actual request in a retry loop
		$try_count   = 1;
		$max_retries = $this->request_retries();

		while ( true ) {
			$start_time = current_time( 'timestamp' );
			
			$parsed_response = $this->curl( $request_url, $request_args, true, $method );
			
			$end_time = current_time( 'timestamp' );

			WooSquare_Sync_Logger::log( sprintf( '%s', $debug_label ), $start_time, $end_time );

			// check for error request and log it
			if ( is_object( $parsed_response['decoded_body'] ) && ! empty( $parsed_response['decoded_body']->type ) ) {
				if ( preg_match( '/bad_request/', $parsed_response['decoded_body']->type ) || preg_match( '/not_found/', $parsed_response['decoded_body']->type ) )  {
					WooSquare_Sync_Logger::log( sprintf( '%s - %s', $parsed_response['decoded_body']->type, $parsed_response['decoded_body']->message ), $start_time, $end_time );

					return false;
				}
			}

			// handle expired tokens
			if ( is_object( $parsed_response['decoded_body'] ) && 
				( ! empty( $parsed_response['decoded_body']->type ) && 'oauth.expired' === $parsed_response['decoded_body']->type ) || 
				( ! empty( $parsed_response['decoded_body']->errors ) && 'ACCESS_TOKEN_EXPIRED' === $parsed_response['decoded_body']->errors[0]->code ) ) {
				
				$oauth_connect_url = 'https://connect.woocommerce.com/renew/square';

				if ( WC_SQUARE_ENABLE_STAGING ) {
					$oauth_connect_url = 'https://connect.woocommerce.com/renew/squaresandbox';
				}
				
				$args = array(
					'body' => array(
						'token' => $this->access_token
					),
					'timeout' => 45,
				);

				$start_time            = current_time( 'timestamp' );
				$parsed_oauth_response = $this->curl( $oauth_connect_url, $args, false, 'POST' );
				$end_time              = current_time( 'timestamp' );

				if ( $parsed_oauth_response['curl_error'] ) {

					WooSquare_Sync_Logger::log( sprintf( 'Renewing expired token error - %s', $parsed_oauth_response['curl_error'] ), $start_time, $end_time );
					
					return false;

				} elseif ( is_object( $parsed_oauth_response['decoded_body'] ) && ! empty( $parsed_oauth_response['decoded_body']->error ) ) {

					WooSquare_Sync_Logger::log( sprintf( 'Renewing expired token error - %s', $parsed_oauth_response['decoded_body']->type ), $start_time, $end_time );
					
					return false;

				} elseif ( 500 === $parsed_oauth_response['response_code'] ) { 
					WooSquare_Sync_Logger::log( sprintf( 'Renewing expired token error - Internal Server Error 500 from ' . $oauth_connect_url ), $start_time, $end_time );

					return false;

				} elseif ( is_object( $parsed_oauth_response['decoded_body'] ) && ! empty( $parsed_oauth_response['decoded_body']->access_token ) ) {
					update_option( 'woocommerce_square_merchant_access_token', sanitize_text_field( urldecode( $parsed_oauth_response['decoded_body']->access_token ) ) );

					// let's set the token instance again so settings option is refreshed
					$this->set_access_token( sanitize_text_field( urldecode( $parsed_oauth_response['decoded_body']->access_token ) ) );
					$request_args['headers']['Authorization'] = 'Bearer ' . sanitize_text_field( $this->get_access_token() );

					WooSquare_Sync_Logger::log( sprintf( 'Retrying with new refreshed token' ), $start_time, $end_time );

					// start at the beginning again
					continue;
				} else {
					WooSquare_Sync_Logger::log( sprintf( 'Renewing expired token error - Unknown Error' ), $start_time, $end_time );

					return false;
				}
			}

			// handle revoked tokens
			if ( is_object( $parsed_response['decoded_body'] ) && ! empty( $parsed_response['decoded_body']->type ) && 'oauth.revoked' === $parsed_response['decoded_body']->type ) {
				WooSquare_Sync_Logger::log( sprintf( 'Token is revoked!' ), $start_time, $end_time );
				
				return false;
			}

			if ( $parsed_response['curl_error'] ) {
				WooSquare_Sync_Logger::log( sprintf( '(%s) Try #%d - %s', $debug_label, $try_count, $parsed_response['curl_error'] ), $start_time, $end_time );
			} else {

				return $parsed_response;

			}

			$try_count++;

			if ( $try_count > $max_retries ) {
				break;
			}

			sleep( 1 );

		}

		return false;

	}

	/**
	 * Performs a cURL request
	 * 
	 * @version 1.0.7
	 * @since 1.0.7
	 */
	private function curl( $request_url = '', $request_args = array(), $headers = false, $method = 'GET' ) {
		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, untrailingslashit( $request_url ) );
		
		switch( $method ) {
			case 'POST':
				curl_setopt( $ch, CURLOPT_POST, 1 );
				break;
			case 'PUT':
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PUT' );
				break;
			case 'DELETE':
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'DELETE' );
				break;
		}

		curl_setopt( $ch, CURLOPT_HEADER, true );

		if ( $headers && ! empty( $request_args['headers'] ) ) {
			$headers   = array();
			$headers[] = 'Authorization: ' . $request_args['headers']['Authorization'];
			$headers[] = 'Accept: ' . $request_args['headers']['Accept'];
			$headers[] = 'Content-Type: ' . $request_args['headers']['Content-Type'];
			$headers[] = 'User-Agent: ' . $request_args['user-agent'];

			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		}
		
		if ( ! empty( $request_args['body'] ) ) {
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $request_args['body'] );
		}

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $request_args['timeout'] );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );

		$response      = curl_exec( $ch );
		$curl_error    = curl_error( $ch ) ? curl_error( $ch ) : false;
		$response_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$header_len    = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
		$headers       = substr( $response, 0, $header_len );
		$body          = substr( $response, $header_len );

		curl_close( $ch );

		return array( 'curl_error' => $curl_error, 'response_code' => $response_code, 'headers' => $headers, 'body' => $body, 'decoded_body' => json_decode( $body ) );
	}
}
