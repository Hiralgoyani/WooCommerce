<?php
 
class Square {

    //Class properties.
    protected $accessToken;
    protected $squareURL;
    protected $locationId;
    protected $mainSquareURL;

    /**
     * Constructor
     *
     * @param object $accessToken
     *
     */
    public function __construct($accessToken, $locationId="me") {
        $this->accessToken = $accessToken;
        if(empty($locationId)){ $locationId = 'me'; }
        $this->locationId = $locationId;
        $this->squareURL = "https://connect.squareup.com/v1/" . $this->locationId;
        $this->mainSquareURL = "https://connect.squareup.com/v1/me";
    }

    
    public function getAccessToken(){
        return $this->accessToken;
    }
    
    public function setAccessToken($access_token){
        $this->accessToken = $access_token;
    }
    
    public function getSquareURL(){
        return $this->squareURL;
    }
    

    public function setLocationId($location_id){        
        $this->locationId = $location_id;
        $this->squareURL = "https://connect.squareup.com/v1/".$location_id;
    }
    
    public function getLocationId(){
        return $this->locationId;
    }
    
    /*
     * authoirize the connect to Square with the given token
     */

    public function authorize() {
        Helpers::debug_log('info', "-------------------------------------------------------------------------------");
        Helpers::debug_log('info', "Authorize square account with Token: " . $this->accessToken);
		$url = $this->mainSquareURL;
		$headers = array(
			'Authorization' => 'Bearer '.$this->accessToken, // Use verbose mode in cURL to determine the format you want for this header
			'Content-Type'  => 'application/json;',
		);
		$method = "GET";
		$args = array('');
		$response = $this->wp_remote_woosquare($url,$args,$method,$headers);
		Helpers::debug_log('info', "The response of authorize curl request" . json_encode($response));
		if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
			$response = json_decode($response['body'], true);
		}
        if (isset($response['id'])) {
            update_option('woo_square_access_token_free', $this->accessToken);
            update_option('woo_square_account_type', $response['account_type']);
            update_option('woo_square_account_currency_code', $response['currency_code']);
            if($response['account_type'] == "LOCATION"){
                update_option('woo_square_location_id_free', 'me');
                update_option('woo_square_locations_free', '');
                update_option('woo_square_business_name_free', $response['business_name']);
            }else{
                $result = $this->getAllLocations();
                if($result){
                    $locations = array();
                    foreach ($result as $location) {
                        $locations[$location['id']] = $location['name'];
                    }
                    $location_id = key($locations);
                    update_option('woo_square_locations_free', $locations);
                    update_option('woo_square_business_name_free', $locations[$location_id]);
                    if($this->locationId == "me")
                        update_option('woo_square_location_id_free', $location_id);
                }
            }
            return true;
        } else {
            return false;
        }
    }
	
	public function wp_remote_woosquare($url,$args,$method,$headers){
		
		// $args = array( 'id' => 1234 );
		// $method = 'GET'; // or 'POST', 'HEAD', etc
		// $headers = array(
			// 'Authorization' => 'Bearer ' . $auth, // Use verbose mode in cURL to determine the format you want for this header
			// 'Accept'        => 'application/json;ver=1.0',
			// 'Content-Type'  => 'application/json; charset=UTF-8',
			// 'Host'          => 'api.example.com'
		// );
		$request = array(
			'headers' => $headers,
			'method'  => $method,
		);

		if ( $method == 'GET' && ! empty( $args ) && is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		} else {
			$request['body'] = json_encode( $args );
		}
		
		$response = wp_remote_request( $url, $request );
		$decoded_response = json_decode( wp_remote_retrieve_body( $response ) );
		
		 /*  */
		if ( is_object( $decoded_response ) &&
				( ! empty( $decoded_response->type ) && 'oauth.expired' === $decoded_response->type ) ||
				( ! empty( $decoded_response->errors ) && 'ACCESS_TOKEN_EXPIRED' === $decoded_response->errors[0]->code ) ) {
				
				$oauth_connect_url = WOOSQU_CONNECTURL;
				
				$args_renew = array(
					'body' => array(
						'header' => $headers,
						'action' => 'renew_token',
					),
					'timeout' => 45,
				);

				$start_time     = current_time( 'timestamp' );
				$oauth_response = wp_remote_post( $oauth_connect_url, $args_renew );
				$end_time       = current_time( 'timestamp' );

				$decoded_oauth_response = json_decode( wp_remote_retrieve_body( $oauth_response ) );
				
				if(!empty($decoded_oauth_response->access_token)){
					$request = array(
						'headers' => array(
										'Authorization' => 'Bearer '.$decoded_oauth_response->access_token, // Use verbose mode in cURL to determine the format you want for this header
										'Content-Type'  => 'application/json;',
									),
						'method'  => $method,
					);
					update_option('woo_square_access_token_free',$decoded_oauth_response->access_token);
					update_option('woo_square_access_token_cauth',$decoded_oauth_response->access_token);
					if ( $method == 'GET' && ! empty( $args ) && is_array( $args ) ) {
						$url = add_query_arg( $args, $url );
					} else {
						$request['body'] = json_encode( $args );
					}
					
					$response = wp_remote_request( $url, $request );
					
				} 
				
		}
		
		return $response;
	}
    
    /*
     * get currency code by location id
     */
    public function getCurrencyCode(){
        
        Helpers::debug_log('info', "Getting currency code for square token: {$this->getAccessToken()}, url: {$this->squareURL} "
        . "and location: {$this->locationId}");
		$url = $this->squareURL;
		$headers = array(
			'Authorization' => 'Bearer '.$this->accessToken, // Use verbose mode in cURL to determine the format you want for this header
			'Content-Type'  => 'application/json;',
		);
		$method = "GET";
		$args = array('');
		$response = $this->wp_remote_woosquare($url,$args,$method,$headers);
		if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
			Helpers::debug_log('info', "The response of current location curl request" . json_encode($response));
			$response = json_decode($response['body'], true);
		}
        if (isset($response['id'])) {
            update_option('woo_square_account_currency_code', $response['currency_code']);
        }
    }
    
    
    
    
    /*
     * get all locations if account type is business
     */

    public function getAllLocations() {
        
		$url = $this->mainSquareURL . '/locations';
		$headers = array(
			'Authorization' => 'Bearer '.$this->accessToken, // Use verbose mode in cURL to determine the format you want for this header
			'Content-Type'  => 'application/json;',
		);
		$method = "GET";
		$args = array('');
		$response = $this->wp_remote_woosquare($url,$args,$method,$headers);
		if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
			Helpers::debug_log('info', "The response of get locations request " . json_encode($response));
			return $response = json_decode($response['body'], true);
		}
    }
	
	/*
	* Update Square inventory based on this order 
	*/
	
    public function completeOrder($order_id) {
       
        
        Helpers::debug_log('info', "Complete Order: " . $order_id);
        $order = new WC_Order($order_id);
        $items = $order->get_items();
        Helpers::debug_log('info', "Order's items" . json_encode($items));
        Helpers::debug_log('info', "Order created by " . $order->get_created_via());
 
        if ($order->get_created_via() == "Square")
            return;
 
        foreach ($items as $item) {
            if ($item['variation_id']) {
                Helpers::debug_log('info', "Variable item");
                if (get_post_meta($item['variation_id'], '_manage_stock', true) == 'yes' or get_post_meta($item['variation_id'], '_manage_stock', true) == '1') {
                    Helpers::debug_log('info', "Item allow manage stock");
                    $product_variation_id = get_post_meta($item['variation_id'], 'variation_square_id', true);
                    Helpers::debug_log('info', "Item variation square id: " . $product_variation_id);
                    $this->updateInventory($product_variation_id, -1 * $item['qty'], 'SALE');
                }
            } else {
                Helpers::debug_log('info', "Simple item");
                if (get_post_meta($item['product_id'], '_manage_stock', true) == 'yes' or get_post_meta($item['product_id'], '_manage_stock', true) == '1') {
                    Helpers::debug_log('info', "Item allow manage stock");
                    $product_variation_id = get_post_meta($item['product_id'], 'variation_square_id', true);
                    Helpers::debug_log('info', "Item variation square id: " . $product_variation_id);
                    $this->updateInventory($product_variation_id, -1 * $item['qty'], 'SALE');
                }
            }
        }
    }
	
	/*
	* create a refund to Square
	*/
 
    public function refund($order_id, $refund_id) {
       
        Helpers::debug_log('info', "Refund Order: " . $order_id);
        $order = new WC_Order($order_id);
        $items = $order->get_items();
        Helpers::debug_log('info', "Order's items" . json_encode($items));
        foreach ($items as $item) {
            if ($item['variation_id']) {
                Helpers::debug_log('info', "Variable item");
                if (get_post_meta($item['variation_id'], '_manage_stock', true) == 'yes' or get_post_meta($item['variation_id'], '_manage_stock', true) == '1') {
                    Helpers::debug_log('info', "Item allow manage stock");
                    $product_variation_id = get_post_meta($item['variation_id'], 'variation_square_id', true);
                    Helpers::debug_log('info', "Item variation square id: " . $product_variation_id);
                    $this->updateInventory($product_variation_id, 1 * $item['qty'], 'RECEIVE_STOCK');
                }
            } else {
                Helpers::debug_log('info', "Simple item");
                if (get_post_meta($item['product_id'], '_manage_stock', true) == 'yes' or get_post_meta($item['product_id'], '_manage_stock', true) == '1') {
                    Helpers::debug_log('info', "Item allow manage stock");
                    $product_variation_id = get_post_meta($item['product_id'], 'variation_square_id', true);
                    Helpers::debug_log('info', "Item variation square id: " . $product_variation_id);
                    $this->updateInventory($product_variation_id, 1 * $item['qty'], 'RECEIVE_STOCK');
                }
            }
        }
		
		
		$woocommerce_square_settings = get_option('woocommerce_square_settings');
		$token           = get_option( 'woocommerce_square_merchant_access_token' );
		if(@$woocommerce_square_settings['enable_sandbox'] == 'yes'){
			$token = $woocommerce_square_settings['sandbox_access_token'];
		}
		
		$fields = array(
			"idempotency_key" => uniqid(),
			"type" => "PARTIAL",
			"payment_id" => get_post_meta($order_id, 'woosquare_transaction_id', true),
			"reason" => "Returned Goods",
			"amount_money" => array(
				  "amount" => (get_post_meta($refund_id, '_refund_amount', true) * 100 ),
				  "currency" => get_post_meta($order_id, '_order_currency', true),
				),
		);
		
		$url = "https://connect.".WC_SQUARE_STAGING_URL.".com/v2/refunds";
		$headers = array(
			'Accept' => 'application/json',
			'Authorization' => 'Bearer '.$token,
			'Content-Type' => 'application/json',
			'Cache-Control' => 'no-cache'
		);
		
		$refund_obj = json_decode(wp_remote_retrieve_body(wp_remote_post($url, array(
				'method' => 'POST',
				'headers' => $headers, 
				'httpversion' => '1.0',
				'sslverify' => false,
				'body' => json_encode($fields)
				)
			)
		)
		);
		
        // $refund_obj = json_decode($response);
		if('APPROVED' === $refund_obj->refund->status || 'PENDING' === $refund_obj->refund->status ){
			$refund_message = sprintf( __( 'Refunded %s - Refund ID: %s ', 'wpexpert-square' ), wc_price( $refund_obj->refund->amount_money->amount / 100 ), $refund_obj->refund->id);
			update_post_meta($order_id, "refund_created_at", $refund_obj->refund->created_at);
			update_post_meta($order_id, "refund_created_id", $refund_obj->refund->id);
			$order->add_order_note( $refund_message );
		}
       
        
    }
	
	
	
	/*
	* Update Inventory with stock amount
	*/

	public function updateInventory($variation_id, $stock, $adjustment_type = "RECEIVE_STOCK") {
		
		
		
		$data_string = array(
			'quantity_delta' => $stock,
			'adjustment_type' => $adjustment_type
		);
		
		$url =  $this->getSquareURL() . '/inventory/' . $variation_id;
		$headers = array(
			'Authorization' => 'Bearer '.$this->accessToken, // Use verbose mode in cURL to determine the format you want for this header
			'Content-Length'  => strlen(json_encode($data_string)),
			'Content-Type'  => 'application/json'
		);
		$method = "POST";
		$args = ($data_string);
		$square = new Square(get_option('woo_square_access_token_free'), get_option('woo_square_location_id_free'));
		$response = $square->wp_remote_woosquare($url,$args,$method,$headers);
		Helpers::debug_log('info', "The response of adding new category curl request " . json_encode($response));
		if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
			$result = json_decode($response['body'], true);
			
			return ($response['response']['code']==200)?true:$result;
		}
		
		
		
	}


    
    
    
}
