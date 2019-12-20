<?php
 
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

/**
 * settings page
 */
function woo_square_settings_page() {
    add_menu_page('Woo Square Settings', 'Woo-Square', 'manage_options', 'square-settings', 'square_settings_page', "dashicons-store");
    add_submenu_page('square-settings', "Square-Payment-Settings", "Square Payment ", 'manage_options', 'Square-Payment', 'square_payment_page_redirection');
    add_submenu_page('square-settings', "Square-Payment-Settings", "Square More <span class='ws-pro-tag'>PRO</span>", 'manage_options', 'Square-Payment-Settings', 'square_payment_plugin_page');
    add_submenu_page('square-settings', "Logs", "Logs", 'manage_options', 'square-logs', 'logs_plugin_page_woosquare');
	add_submenu_page('square-settings', "Documentation", "Documentation", 'manage_options', 'square-documentation', 'documentation_plugin_page');

}

/**
 * Settings page action
 */
function square_settings_page() {
    

    $square = new Square(get_option('woo_square_access_token_free'), get_option('woo_square_location_id_free'));

    $errorMessage = '';
    $successMessage = '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['terminate_sync'])) {
        
        //clear session variables if exists
        if (isset($_SESSION["square_to_woo"])){ unset($_SESSION["square_to_woo"]); };
        if (isset($_SESSION["woo_to_square"])){ unset($_SESSION["woo_to_square"]); };
        
        update_option('woo_square_running_sync', false);
        update_option('woo_square_running_sync_time', 0);
        Helpers::debug_log('info', "Synchronization terminated due to admin request");

        $successMessage = 'Sync terminated Successfully!';
    }
	
	if(
		!empty($_REQUEST['access_token']) and 
		!empty($_REQUEST['token_type']) and 
		$_REQUEST['token_type'] == 'bearer' 
		){
			
		
			if ( function_exists( 'wp_verify_nonce' ) && ! wp_verify_nonce( $_GET['wc_woosquare_token_nonce'], 'connect_woosquare' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'woosquare-square' ) );
			}

			$existing_token = get_option( 'woo_square_access_token_free' );

			// if token already exists, don't continue
			// if (  empty( $existing_token ) OR empty(get_option('woo_square_access_token_cauth')) ) {
				update_option('woo_square_access_token_free',$_REQUEST['access_token']);
				update_option('woo_square_access_token_cauth',$_REQUEST['access_token']);
				update_option('woo_square_update_msg_dissmiss','connected');
				delete_option('woo_square_auth_notice');
			// }
			wp_redirect(add_query_arg(
				array(
					'page'    => 'square-settings',
				),
				admin_url( 'admin.php' )
			));
			exit;
		}
	if(
		!empty($_REQUEST['disconnect_woosquare']) and 
		!empty($_REQUEST['wc_woosquare_token_nonce']) 
	){
		if ( function_exists( 'wp_verify_nonce' ) && ! wp_verify_nonce( $_GET['wc_woosquare_token_nonce'], 'disconnect_woosquare' ) ) {
			wp_die( __( 'Cheatin&#8217; huh?', 'woocommerce-square' ) );
		}
		
		
		
		//revoke token
		$oauth_connect_url = WOOSQU_CONNECTURL;
		$headers = array(
			'Authorization' => 'Bearer '.get_option('woo_square_access_token_free'), // Use verbose mode in cURL to determine the format you want for this header
			'Content-Type'  => 'application/json;',
		);		
		$redirect_url = add_query_arg(
			array(
				'app_name'    => WOOSQU_APPNAME,
				'plug'    => WOOSQU_PLUGIN_NAME,
			),
			admin_url( 'admin.php' )
		);
		$redirect_url = wp_nonce_url( $redirect_url, 'connect_wcsrs', 'wc_wcsrs_token_nonce' );
		$site_url = ( urlencode( $redirect_url ) );
		$args_renew = array(
			'body' => array(
				'header' => $headers,
				'action' => 'revoke_token',
				'site_url'    => $site_url,
			),
			'timeout' => 45,
		);
			
		$oauth_response = wp_remote_post( $oauth_connect_url, $args_renew );

		$decoded_oauth_response = json_decode( wp_remote_retrieve_body( $oauth_response ) );
		
		delete_option('woo_square_access_token_free');
		delete_option('woo_square_location_id_free');
		delete_option('woo_square_access_token_cauth');
		delete_option('woo_square_locations_free');
		delete_option('woo_square_business_name_free');
		wp_redirect(add_query_arg(
			array(
				'page'    => 'square-settings',
			),
			admin_url( 'admin.php' )
		));
			exit;
	}
    
    // check if the location is not setuped
    if (get_option('woo_square_access_token_free') && !get_option('woo_square_location_id_free')) {
        $square->authorize();
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // setup account
        if (isset($_POST['woo_square_access_token_free'])) {
            $square->setAccessToken(sanitize_text_field($_POST['woo_square_access_token_free']));
            if ($square->authorize()) {
                $successMessage = 'Settings updated Successfully!';
            } else {
                $errorMessage = 'Square Account Not Authorized';
            }
        }
        // save settings
        if (isset($_POST['woo_square_settings'])) {
			
            update_option('sync_on_add_edit', intval($_POST['sync_on_add_edit']));
            //update location id
            if( !empty($_POST['woo_square_location_id_free'])){
				// its a textformated like 123abc456 that why we used sanitize_text_field :) 
                $location_id = sanitize_text_field($_POST['woo_square_location_id_free']);
                update_option('woo_square_location_id_free', $location_id);               
                $square->setLocationId($location_id);
                $square->getCurrencyCode();
            }

            $successMessage = 'Settings updated Successfully!';
        }
    }
    $wooCurrencyCode    = get_option('woocommerce_currency');
    $squareCurrencyCode = get_option('woo_square_account_currency_code');
    
    if(!$squareCurrencyCode){
        $square->getCurrencyCode();
        $squareCurrencyCode = get_option('woo_square_account_currency_code');
    }
    if ( $currencyMismatchFlag = ($wooCurrencyCode != $squareCurrencyCode) ){
        Helpers::debug_log('info', "Currency code mismatch between Square [$squareCurrencyCode] and WooCommerce [$wooCurrencyCode]");

    }
    include WOO_SQUARE_PLUGIN_PATH . 'views/settings.php';
}


function square_payment_page_redirection(){
	wp_redirect(admin_url().'admin.php?page=wc-settings&tab=checkout&section=square');
	wp_die();
}

function documentation_plugin_page(){
	wp_redirect('https://apiexperts.io/documentation/topics/woosquare/');
	wp_die();
}

/**
 * Logs page action
 * @global type $wpdb
 */
function logs_plugin_page_woosquare(){
        
      
        global $wpdb;
        
        $query = "
        SELECT log.id as log_id,log.action as log_action, log.date as log_date,log.sync_type as log_type,log.sync_direction as log_direction, children.*
        FROM ".$wpdb->prefix.WOO_SQUARE_TABLE_SYNC_LOGS." AS log
        LEFT JOIN ".$wpdb->prefix.WOO_SQUARE_TABLE_SYNC_LOGS." AS children
            ON ( log.id = children.parent_id )
        WHERE log.action = %d ";
              
        $parameters = [Helpers::ACTION_SYNC_START];
        
        //get the post params if sent or 'any' option was not chosen
        $sync_type = (isset($_POST['log_sync_type']) && strcmp($_POST['log_sync_type'],'any')) ? sanitize_text_field($_POST['log_sync_type']):null;
        $sync_direction = (isset($_POST['log_sync_direction']) && strcmp($_POST['log_sync_direction'],'any'))? sanitize_text_field($_POST['log_sync_direction']):null;
        $sync_date = isset($_POST['log_sync_date'])?
            (strcmp($_POST['log_sync_date'],'any')? sanitize_text_field($_POST['log_sync_date']):null):1;

        
        if (!is_null($sync_type)){
            $query.=" AND log.sync_type = %d ";
            $parameters[] = $sync_type; 
        }
        if (!is_null($sync_direction)){
           $query.=" AND log.sync_direction = %d ";
           $parameters[] = $sync_direction;  
        }
        if (!is_null($sync_date)){
           $query.=" AND log.date > %s ";
           $parameters[] = date("Y-m-d H:i:s", strtotime("-{$sync_date} days"));
        }
        
        
        $query.="
            ORDER BY log.id DESC,
                     id ASC";

        $sql =$wpdb->prepare($query, $parameters);
        $results = $wpdb->get_results($sql);
        $helper = new Helpers();
        
        include WOO_SQUARE_PLUGIN_PATH . 'views/logs.php';
       
}

/**
 * square payment plugin pro page action
 * @global type $wpdb
 */
function square_payment_plugin_page(){
    $html1 = '<h1 class="ws-heading-pro">Woo Square PRO</h1>';
    $html1 .= '<h2 class="ws-pro-ver">Why Use Pro Version?</h3>';
    $html1 .= '<div class="ws-pro-describe"><div class="ws-descrive-para">Need for that to simplify the process of selling data and integration between woo commerce and customers who use square point of sale at their transactions without need to adjust the inventory at both sides Synchronize products categories-products-products variations-discounts –quantity –price between square & woo commerce.
Synchronize Any updates at products details.Synchronize Customers create orders ,all orders details at square must be synchronized at woo commerce with products quantity deduction
There will be options if the system contain same products SKUs ,available options:
- Woo commerce product Override square product – Square product Override Woo commerce product<div class="ws-download-link"><a href="https://codecanyon.net/item/woosquare/14663170">Download Now</a></div></div><div class="ws-pro-img"><img src="'.WOO_SQUARE_PLUGIN_URL_FREE.'_inc/images/woo-square-pro.png" ></div>';
    
    $html = '<div class="wrap about-wrap full-width-layout">';
    
    $html .= '<div id="woosquare_integration" class="ws-pro-wrapper">
            <div class="ws-head-txt">
            <h2>Switch to <strong>WooSquare Pro</strong> for more features..</h2>
            <p class="about-text">Want some more features other than synchronizing products between Square to WooCommerce and WooCommerce to Square?
No worries!! You can avail more feature in WooSquare Pro.</p></div>
        
        <div class="ws-pro-box">
            <div class="ws-pro-box-img"><span class="dashicons dashicons-update"></span></div>
            <div class="ws-pro-title"><h3>Order Synchronization from Square to WooCommerce</h3></div>
            <div class="ws-pro-para">In WooSquare Pro all your orders will synchronize from Square to WooCommerce. Even your refunds and stock will be synchronized.</div>
        </div>
		
		<div class="ws-pro-box">
            <div class="ws-pro-box-img"><span class="dashicons dashicons-list-view" style="color: #3a3b40"></span></div>
            <div class="ws-pro-title"><h3>Advance Attribute support</h3></div>
            <div class="ws-pro-para">Do you have variable products with multiple attributes or even you would like to sync attributes for your simple products. Now its doable with WooSquare Pro.</div>
           
        </div>
		<div class="ws-pro-box">
            <div class="ws-pro-box-img"><img src="https://apiexperts.io/wp-content/uploads/2018/01/square-documentation.png" width="100" alt="Detail documentation for everything" /></div>
            <div class="ws-pro-title"><h3>Detail documentation for everything</h3></div>
            <div class="ws-pro-para">we have covered and documented everything for you so that while using the plugin WooSquare Pro you are never going to stuck anywhere </div>
                      
        </div>
		
		<div class="ws-pro-box ext">
            <div class="ws-pro-box-img"><span class="dashicons dashicons-update"></span></div>
            <div class="ws-pro-title"><h3>Order Synchronization from WooCommerce to Square</h3></div>
            <div class="ws-pro-para">In WooSquare Pro all your orders will synchronize from WooCommerce to Square. Even your refunds and stock will be synchronized. </div>
                      
        </div>
		
		<div class="ws-pro-box">
            <div class="ws-pro-box-img"><span class="dashicons dashicons-editor-alignleft"></span></div>
            <div class="ws-pro-title"><h3>Custom Transaction Notes Addon</h3></div>
            <div class="ws-pro-para">Custom Transaction Note will show you WooCommerce checkout fields ( billings ) and order ID at Square transaction note which you have choose from transaction Note settings while using WooSquare Pro Plugin.</div>
                      
        </div>
		
        <div class="ws-download-link"><a href="https://goo.gl/LEJeQG">BUY WOOSQUARE PRO</a></div>
    </div>';
	$html .= '<div id="woosquare_integration_more" class="ws-pro-wrapper">
            <div class="ws-head-txt"><h1>Looking For More <a href="https://apiexperts.io/solutions/">Square Integrations</a>?</h1>
            </div>
        <div id="main_box_row">
		<div class="ws-pro-box">
            <div class="ws-pro-box-img"><img src="https://apiexperts.io/wp-content/uploads/2018/01/woo-square-subscription-recuring.png" alt="SQUARE RECURRING PAYMENTS FOR WOOCOMMERCE SUBSCRIPTIONS"></div>
            <div class="ws-pro-title"><h3>SQUARE RECURRING PAYMENTS FOR WOOCOMMERCE SUBSCRIPTIONS</h3></div>
            <div class="ws-pro-para"><p>WooCommerce plugin that allows you to pay for your subscription through square recurring payment..</p></div>
        <div class="ws-download-link"><a href="https://apiexperts.io/solutions/square-recurring-payments-for-woocommerce-subscriptions/">Buy Now</a></div>
		</div>
        <div class="ws-pro-box">
            <div class="ws-pro-box-img"><img src="https://apiexperts.io/wp-content/uploads/2018/01/give-quare.jpg" alt="SQUARE FOR GIVEWP"></div>
            <div class="ws-pro-title"><h3>SQUARE FOR GIVEWP</h3></div>
            <div class="ws-pro-para"><p>WordPress plugin that allows users to pay for their donations using Square payment gateway through GiveWP..</p></div>
        <div class="ws-download-link"><a href="https://apiexperts.io/solutions/square-for-givewp/">Buy Now</a></div>
		</div>
        <div class="ws-pro-box">
            <div class="ws-pro-box-img"><img src="https://apiexperts.io/wp-content/uploads/2018/01/gravity-forms-square-payment-gateway.png" alt="SQUARE FOR
GRAVITY FORMS"></div>
            <div class="ws-pro-title"><h3>SQUARE FOR GRAVITY FORMS</h3></div>
            <div class="ws-pro-para"><p>WordPress plugin that allows users to pay from their gravity form using Square payment gateway..</p></div>
        <div class="ws-download-link"><a href="https://apiexperts.io/square-for-gravity-forms/">Buy Now</a></div>
		</div>
		<div class="ws-pro-box"><!--second row-->
            <div class="ws-pro-box-img"><img src="https://apiexperts.io/wp-content/uploads/2018/04/square-payment-getway-for-ninja-forms.png" alt="Square for Ninja forms"></div>
            <div class="ws-pro-title"><h3>SQUARE INTEGRATION WITH NINJA FORMS</h3></div>
            <div class="ws-pro-para"><p>Ninja Forms Square plugin is a WordPress plugin that allow customers to pay for..</p></div>
        <div class="ws-download-link"><a href="http://apiexperts.io/solutions/square-integration-with-ninja-forms/">Buy Now</a></div>
		</div>
		        <div class="ws-pro-box">
            <div class="ws-pro-box-img"><img src="https://apiexperts.io/wp-content/uploads/2018/05/gravity-forms-square-recurring-1-300x300.png" alt="SQUARE FOR
GRAVITY FORMS"></div>
            <div class="ws-pro-title"><h3>GRAVITY FORMS WITH SQUARE RECURRING PAYMENT</h3></div>
            <div class="ws-pro-para"><p>Gravity Form Square Recurring plugin is a WordPress plugin that allow customers to..</p></div>
        <div class="ws-download-link"><a href="https://apiexperts.io/solutions/gravity-forms-with-square-recurring-payment/">Buy Now</a></div>
		</div>
		        <div class="ws-pro-box">
            <div class="ws-pro-box-img"><img src="https://apiexperts.io/wp-content/uploads/2018/04/caldera-forms-with-square.png" alt="SQUARE FOR
GRAVITY FORMS"></div>
            <div class="ws-pro-title"><h3  style="padding:15px 10px 0px;">PAY WITH SQUARE IN CALDERA FORM</h3></div>
            <div class="ws-pro-para"><p>Square For Caldera Form plugin is a WordPress plugin that allows users to pay for their caldera form using Square payment..</p></div>
        <div class="ws-download-link"><a href="https://apiexperts.io/solutions/pay-with-square-in-caldera-form/">Buy Now</a></div>
		</div>		
				        <div class="ws-pro-box">
            <div class="ws-pro-box-img"><img src="http://apiexperts.io/wp-content/uploads/2018/08/easy-digital-downloads-with-square-getway-1.png" alt="SQUARE FOR
GRAVITY FORMS"></div>
            <div class="ws-pro-title"><h3>EASY DIGITAL DOWNLOADS WITH SQUARE</h3></div>
            <div class="ws-pro-para"><p>Square Easy Digital Downloads EDD Payment plugin is a WordPress plugin that allows you to pay for your digital downloads..</p></div>
        <div class="ws-download-link"><a href="https://apiexperts.io/solutions/easy-digital-downloads-with-square/">Buy Now</a></div>
		</div>	
				        <div class="ws-pro-box">
            <div class="ws-pro-box-img"><img src="https://apiexperts.io/wp-content/uploads/2018/04/wp-easy-pay-for-square-1-285x300.png" alt="SQUARE FOR
GRAVITY FORMS"></div>
            <div class="ws-pro-title"><h3>WP EASY PAY FOR WORDPRESS</h3></div>
            <div class="ws-pro-para"><p>WordPress Easy Pay plugin allows you to easily create Buy Now, Donation or Subscription type buttons. It generates dynamic buttons using shortcodes that..</p></div>
        <div class="ws-download-link"><a href="https://apiexperts.io/solutions/wp-easy-pay-wordpress/">Buy Now</a></div>
		</div>	
		<div class="ws-pro-box">
            <div class="ws-pro-box-img"><img src="https://apiexperts.io/wp-content/uploads/2018/01/woosquare-pro-1-298x300.png" alt="SQUARE FOR
GRAVITY FORMS"></div>
            <div class="ws-pro-title"><h3>MANAGE INVENTORY, AUTO SYNC & ACCEPT ONLINE PAYMENTS</h3></div>
            <div class="ws-pro-para"><p>Our Sync WooCommerce with Square extension will help you to migrate & synchronize your products and categories..</p></div>
        <div class="ws-download-link"><a href="https://codecanyon.net/item/woosquare/14663170?ref=wpexpertsio">Buy Now</a></div>
		</div>	
		<div class="ws-pro-box">
            <div class="ws-pro-box-img"><img style="width:70%;" src="https://apiexperts.io/wp-content/uploads/2018/02/woocommerce-square-up-payment-gateway-1-300x121.png" alt="SQUARE FOR
GRAVITY FORMS"></div>
            <div class="ws-pro-title"><h3>WOOCOMMERCE SQUARE PAYMENT GATEWAY</h3></div>
            <div class="ws-pro-para"><p>It helps you to pay with Square at WooCommerce checkout. With this plugin you can even manage refunds..</p></div>
        <div class="ws-download-link"><a href="https://codecanyon.net/item/woocommerce-square-up-payment-gateway/19692778?ref=wpexpertsio">Buy Now</a></div>
		</div>	
		<div class="ws-pro-box">
            <div class="ws-pro-box-img"><img style="width:25%;" src="http://apiexperts.io/wp-content/uploads/2018/08/woocommerce-order-sync-square-1.png" alt="SQUARE FOR
GRAVITY FORMS"></div>
            <div class="ws-pro-title"><h3>WOOCOMMERCE TO SQUARE ORDER SYNC ADD-ON</h3></div>
            <div class="ws-pro-para"><p>Order itemization will help you to send item information like taxes, discount, and breakdown..</p></div>
        <div class="ws-download-link"><a href="https://apiexperts.io/solutions/woocommerce-square-order-sync-add/">Buy Now</a></div>
		</div>	
		<div class="ws-pro-box">
			<div class="ws-pro-box-img"><img style="width: 33%;" src="https://apiexperts.io/wp-content/uploads/2018/02/square-payment-getway-contact-form7-300x259.png" alt="SQUARE FOR
	GRAVITY FORMS"></div>
			<div class="ws-pro-title"><h3>CONTACT FORM 7 SQUARE PAYMENT ADDON</h3></div>
			<div class="ws-pro-para"><p>Contact Form 7 Square plugin is a WordPress plugin that allows users to pay from their..</p></div>
		<div class="ws-download-link"><a href="https://goo.gl/USTSa7">Buy Now</a></div>
		</div>
        </div>
    </div></div>';
    echo $html;
}