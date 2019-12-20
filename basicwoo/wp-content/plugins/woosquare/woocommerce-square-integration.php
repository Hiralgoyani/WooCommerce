<?php 
/*
  Plugin Name: WooSquare
  Plugin URI: https://wpexperts.io/products/woosquare/
  Description: WooSquare purpose is to migrate & synchronize data (sales –customers-invoices-products inventory) between Square system point of sale & Woo commerce plug-in. 
  Version: 3.2.2
  Author: Wpexpertsio
  Author URI: https://wpexperts.io/
  License: GPLv2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


function report_error() {
	$class = 'notice notice-error';
	if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		$message = __( 'To use "WooSquare - WooCommerce Square Integration" WooCommerce must be activated or installed!', 'woosquare' );
		printf( '<br><div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
	}
	if (version_compare( PHP_VERSION, '5.5.0', '<' )) {
		$message = __( 'To use "WooSquare - WooCommerce Square Integration" PHP version 5.5.0+, Current version is: ' . PHP_VERSION . "\n", 'woosquare' );
		printf( '<br><div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
	}
	
	if(in_array('woosquare-pro/woocommerce-square-integration.php', apply_filters('active_plugins', get_option('active_plugins')))){
		$message = __( 'To use "WooSquare - WooCommerce Square Integration Free deactivate Pro version!', 'woosquare' );
		printf( '<br><div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
	}
	deactivate_plugins('woosquare/woocommerce-square-integration.php');
	wp_die('','Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );

}
	


if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))
	or 
	version_compare( PHP_VERSION, '5.5.0', '<' )
	or
	in_array('woosquare-pro/woocommerce-square-integration.php', apply_filters('active_plugins', get_option('active_plugins')))
	) {
		add_action( 'admin_notices', 'report_error' );
} else {
define('WOO_SQUARE_VERSION','3.0');	
define('WOO_SQUARE_PLUGIN_URL_FREE', plugin_dir_url(__FILE__));
define('WOO_SQUARE_PLUGIN_PATH', plugin_dir_path(__FILE__));

define('WOO_SQUARE_TABLE_DELETED_DATA','woo_square_integration_deleted_data');
define('WOO_SQUARE_TABLE_SYNC_LOGS','woo_square_integration_logs');
define( 'WooSquare_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );


//connection auth credentials
if( !function_exists('get_plugin_data') ){
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$plugin_data = get_plugin_data( __FILE__ );

$WOOSQU_PLUGIN_NAME = $plugin_data['Name'];
if (!defined('WOOSQU_PLUGIN_NAME')) define('WOOSQU_PLUGIN_NAME',$WOOSQU_PLUGIN_NAME);
if (!defined('WOOSQU_CONNECTURL')) define('WOOSQU_CONNECTURL','http://connect.apiexperts.io');
if (!defined('WOOSQU_APPID')) define('WOOSQU_APPID','sq0idp-OkzqrnM_vuWKYJUvDnwT-g');
if (!defined('WOOSQU_APPNAME')) define('WOOSQU_APPNAME','API Experts');




$woocommerce_square_settings = get_option('woocommerce_square_settings');

if($woocommerce_square_settings['enable_sandbox'] == 'yes'){
	if ( ! defined( 'WC_SQUARE_ENABLE_STAGING' ) ) {
		define( 'WC_SQUARE_ENABLE_STAGING', true );
		define( 'WC_SQUARE_STAGING_URL', 'squareupsandbox' );
	}
} else {
	if ( ! defined( 'WC_SQUARE_ENABLE_STAGING' ) ) {
		define( 'WC_SQUARE_ENABLE_STAGING', false );
		define( 'WC_SQUARE_STAGING_URL', 'squareup' );
	}
}

//max sync running time
define('WOO_SQUARE_MAX_SYNC_TIME',600*200);
define( 'WooSquare_VERSION', '1.0.11' );
add_action('admin_menu', 'woo_square_settings_page');
add_action('admin_enqueue_scripts', 'woo_square_script');
add_action('wp_ajax_manual_sync', "woo_square_manual_sync");

add_action( 'init', 'public_init_woosquare',1 );

$sync_on_add_edit = get_option( 'sync_on_add_edit', $default = false ) ;
if($sync_on_add_edit == '1'){
	add_action('save_post', 'woo_square_add_edit_product', 10, 3);
	add_action('before_delete_post', 'woo_square_delete_product');
	add_action('delete_product_cat', 'woo_square_delete_category',10,3);
	add_action('create_product_cat', 'woo_square_add_category');
	add_action('edited_product_cat', 'woo_square_edit_category');
}

add_action('woocommerce_order_refunded', 'woo_square_create_refund', 10, 2);
add_action('woocommerce_order_status_processing', 'woo_square_complete_order');

add_action( 'wp_loaded','woo_square_post_savepage_load_admin_notice' );
add_action( 'admin_notices', 'admin_notice_square' );
require_once( '_inc/square_freemius.php' );
register_activation_hook(__FILE__, 'square_plugin_activation_free');

//import classes
// define('WP_DEBUG_DISPLAY', true);
// define('WP_DEBUG_LOG', true);
@ini_set('display_errors', E_ALL);
require_once WOO_SQUARE_PLUGIN_PATH . '_inc/square.class.php';
require_once WOO_SQUARE_PLUGIN_PATH . '_inc/Helpers.class.php';
require_once WOO_SQUARE_PLUGIN_PATH . '_inc/WooToSquareSynchronizer.class.php';
require_once WOO_SQUARE_PLUGIN_PATH . '_inc/SquareToWooSynchronizer.class.php';
require_once WOO_SQUARE_PLUGIN_PATH . '/_inc/admin/ajax.php';
require_once WOO_SQUARE_PLUGIN_PATH . '/_inc/admin/pages.php';


add_action( 'plugins_loaded', 'payment_init' );
function payment_init(){
	require_once WOO_SQUARE_PLUGIN_PATH . '/_inc/SquareClient.class.php' ;
	require_once WOO_SQUARE_PLUGIN_PATH . '/_inc/SquareSyncLogger.class.php' ;
	require_once WOO_SQUARE_PLUGIN_PATH . '/_inc/payment/SquarePaymentLogger.class.php' ;
	require_once WOO_SQUARE_PLUGIN_PATH . '/_inc/payment/SquarePayments.class.php' ;
}



function admin_notice_square() {
	
	if (  get_option('square_notice_dismiss') == 'no' ) {
        return;
    }
	
	$class = 'notice notice-info is-dismissible square-first-notice';
	$heading = __( 'Good News For Square Users', 'woosquare' );
	$message = __( 'Now we have launched square as <a target="_blank" href="https://goo.gl/s74bht"><b>Payment gateway for Gravity Forms</b></a>, <a  target="_blank" href="https://goo.gl/6jcD3E"><b>GiveWP </b></a>, <a  target="_blank" href="https://goo.gl/A8Yb3P"><b>Square Integration With Ninja Forms </b></a>, <a  target="_blank" href="https://goo.gl/28JCa6"><b>Square Recurring Payments for WooCommerce Subscriptions</b></a>, <a  target="_blank" href="https://goo.gl/USTSa7"><b>Contact Form 7 Square Payment Addon</b></a>, <a  target="_blank" href="https://goo.gl/fL2uPT"><b>Pay With Square In Caldera Form</b></a>, <a  target="_blank" href="https://goo.gl/Ztafpp"><b>Easy Digital Downloads With Square</b></a>, <a  target="_blank" href="https://goo.gl/44XrSX"><b>Wp Easy Pay For Wordpress</b></a>, <a  target="_blank" href="https://goo.gl/CofmvH"><b>Manage Inventory, Auto Sync & Accept Online Payments</b></a>, <a  target="_blank" href="https://goo.gl/nRdRCD"><b>Woocommerce Square Payment Gateway</b></a> and <a  target="_blank" href="https://goo.gl/mquNkq"><b>WooCommerce To Square Order Sync Add-On</b></a> Check out <a  target="_blank" href="https://apiexperts.io/"><b>apiexperts.com</b></a> for more info.' );

	printf( '<div data-dismissible="notice-one-forever-woosquare" class="%1$s"><h2>%2$s</h2><p>%3$s</p></div>', esc_attr( $class ), esc_html( $heading ) ,  $message  ); 
	
	if(version_compare( WOO_SQUARE_VERSION, '3.0', '=' ) 
		and 
	empty(get_option('woo_square_update_msg_dissmiss'))
		and @$_GET['page'] != 'square-settings'
	){
		if(!empty(get_option('woo_square_account_type')) and !empty(get_option('woo_square_account_currency_code'))){
			echo '<div class="error"><p>' . sprintf( __( 'WooSquare is updated successfully. To Contino your product sync and to use the latest sdk connect your Square account , %1$sconnect your Square Account.%2$s', 'woocommerce-square' ), '<a href="' . admin_url( 'admin.php?page=square-settings' ) . '">', '</a>' ) . '</p></div>';
		}
	}
	
	if(!empty(get_option('woo_square_auth_notice'))){
		$class = 'notice error square-first-notice';
		// $heading = __( '', 'woosquare' );
		$message = __( get_option('woo_square_auth_notice') );

		printf( '<div data-dismissible="notice-one-forever-woosquare" class="%1$s"><p>%2$s %3$sConnect your Square Account.%4$s</p> <p>Click here to see %5$sinstructions.%6$s </p></div>', esc_attr( $class ),   $message,  '<a href="' . admin_url( 'admin.php?page=square-settings' ) . '">', '</a>' ,  '<a href="https://apiexperts.io/documentation/faq/install-activate-woosquare/">', '</a>'  ); 
		
	}
	
}



function woo_square_checkOrAddPluginTables(){
    //create tables
	require_once  ABSPATH . '/wp-admin/includes/upgrade.php' ;
    global $wpdb;
	
    $del_prod_table = $wpdb->prefix.WOO_SQUARE_TABLE_DELETED_DATA;
	
    if ($wpdb->get_var("SHOW TABLES LIKE '$del_prod_table'") != $del_prod_table) {

        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

      $sql = "CREATE TABLE " . $del_prod_table . " (
			`square_id` varchar(50) NOT NULL,
                        `target_id` bigint(20) NOT NULL,
                        `target_type` tinyint(2) NULL,
                        `name` varchar(255) NULL,
			PRIMARY KEY (`square_id`)
		) $charset_collate;";
		
        dbDelta($sql);
    }
    
    //logs table
    $sync_logs_table = $wpdb->prefix.WOO_SQUARE_TABLE_SYNC_LOGS;
    if ($wpdb->get_var("SHOW TABLES LIKE '$sync_logs_table'") != $sync_logs_table) {

        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE " . $sync_logs_table . " (
                    `id` bigint(20) auto_increment NOT NULL,
                    `target_id` bigint(20) NULL,
                    `target_type` tinyint(2) NULL,
                    `target_status` tinyint(1) NULL,
                    `parent_id` bigint(20) NOT NULL default '0',
                    `square_id` varchar(50) NULL,
                    `action`  tinyint(3) NOT NULL,
                    `date` TIMESTAMP NOT NULL,
                    `sync_type` tinyint(1) NULL,
                    `sync_direction` tinyint(1) NULL,
                    `name` varchar(255) NULL,
                    `message` text NULL,
                    PRIMARY KEY (`id`)
            ) $charset_collate;";
        dbDelta($sql);
    }
}

/*
 * square activation
 */

function square_plugin_activation_free() {
    $user_id = username_exists('square_user');
    if (!$user_id) {
        $random_password = wp_generate_password(12);
        $user_id = wp_create_user('square_user', $random_password);
        wp_update_user(array('ID' => $user_id, 'first_name' => 'Square', 'last_name' => 'User'));
    }
	
	//create plugin tables when plugin activate..
	woo_square_checkOrAddPluginTables();
    // update_option('woo_square_merging_option', 1);
    update_option('sync_on_add_edit', 1);
	update_option('html_sync_des','');

	if(empty(get_option('square_notice_dismiss'))){
		update_option('square_notice_dismiss','yes');	
	}
    
	
}

/**
 * include script
 */
function woo_square_script() {
    wp_enqueue_script('woo_square_script', WOO_SQUARE_PLUGIN_URL_FREE . '_inc/js/script.js', array('jquery')); 
    wp_localize_script('woo_square_script', 'myAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

    wp_enqueue_style('woo_square_pop-up', WOO_SQUARE_PLUGIN_URL_FREE . '_inc/css/pop-up.css');
    wp_enqueue_style('woo_square_synchronization', WOO_SQUARE_PLUGIN_URL_FREE . '_inc/css/synchronization.css');
}

/*
 * Ajax action to execute manual sync
 */

function woo_square_manual_sync() {
    
    ini_set('max_execution_time', 0);
    
    if(!get_option('woo_square_access_token_free')){
        return;
    }
    
    if(get_option('woo_square_running_sync') && (time()-(int)get_option('woo_square_running_sync_time')) < (WOO_SQUARE_MAX_SYNC_TIME) ){
        Helpers::debug_log('error',"Manual Sync Request: There is already sync running");
        echo 'There is another Synchronization process running. Please try again later. Or <a href="'. admin_url('admin.php?page=square-settings&terminate_sync=true').'" > terminate now </a>';
        die();
    }
    
    update_option('woo_square_running_sync', true);
    update_option('woo_square_running_sync_time', time());
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

        $sync_direction = sanitize_text_field($_GET['way']);
        $square = new Square(get_option('woo_square_access_token_free'),get_option('woo_square_location_id_free'));
        if ($sync_direction == 'wootosqu') {
            $squareSynchronizer = new WooToSquareSynchronizer($square);
            $squareSynchronizer->syncFromWooToSquare();
        } else if ($sync_direction == 'squtowoo') {          
            $squareSynchronizer = new SquareToWooSynchronizer($square);
            $squareSynchronizer->syncFromSquareToWoo();
        }
    }
    update_option('woo_square_running_sync', false);
    update_option('woo_square_running_sync_time', 0);
    die();
}


function public_init_woosquare(){
	 
	if(!empty(get_option('woo_square_access_token_free')) and empty(get_option('woo_square_access_token_cauth'))){
		// delete_option('woo_square_merging_option');
		delete_option('woo_square_access_token_free');
		delete_option('woo_square_account_type');
		delete_option('woo_square_account_currency_code');
		delete_option('woo_square_locations_free');
		delete_option('woo_square_business_name_free');
		delete_option('woo_square_location_id_free');
		update_option('woo_square_auth_notice','WooSquare is updated successfully. In order to sync products and use the latest SDK connect your Square account ,');
	}
}
	
	
	function wp_remote_post_for_mps($url,$body,$contenttype=null){
		if($contenttype == 'form'){
			$contype='application/x-www-form-urlencoded'; 
		} else {
			$contype='application/json';
		}
		if($contenttype != 'array'){
			$headers = array(
				"accept" => "application/json",
				"cache-control" => "no-cache",
				"content-type" => $contype
			);
		} else {
			$headers = array();
		}
		$response = wp_remote_post( $url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => $headers,
			'body' => $body,
			'cookies' => array()
			)
		);
		return $response;
	}		
	
function woo_square_post_savepage_load_admin_notice() {
	// Use html_compress($html) function to minify html codes.
	if(!empty($_GET['post'])){
		$Gpost = sanitize_text_field($_GET['post']);
	
		$admin_notice_square = get_post_meta($Gpost, 'admin_notice_square', true);
		if(!empty($admin_notice_square)){
			echo '<div class="notice notice-error"><p>'.$admin_notice_square.'</p></div>';
			delete_post_meta($Gpost, 'admin_notice_square', 'Product unable to sync to Square due to Sku missing ');
		}
	}	
}

/*
 * Adding and editing new product
 */
function woo_square_add_edit_product($post_id, $post, $update) {
	// checking Would you like to synchronize your product on every product edit or update ?   
	$sync_on_add_edit = get_option( 'sync_on_add_edit', $default = false ) ;
	if($sync_on_add_edit == '1'){
			
		//Avoid auto save from calling Square APIs.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		   
		if ($update && $post->post_type == "product" && $post->post_status == "publish") {
			
			update_post_meta($post_id, 'is_square_sync', 0);
			Helpers::debug_log('info',"[add_update_product_hook] Start updating product on Square");

			if(!get_option('woo_square_access_token_free')){
				return;
			}
			
			$product_square_id = get_post_meta($post_id, 'square_id', true);
			$square = new Square(get_option('woo_square_access_token_free'),get_option('woo_square_location_id_free'));
			
			$squareSynchronizer = new WooToSquareSynchronizer($square);       
			$result = $squareSynchronizer->addProduct($post, $product_square_id);

			$termid = get_post_meta($post_id, '_termid', true);
			if ($termid == '') {//new product
				$termid = 'update';
			}
			update_post_meta($post_id, '_termid', $termid);
			
			if( $result===TRUE ){
				update_post_meta($post_id, 'is_square_sync', 1);  
			}

			Helpers::debug_log('info',"[add_update_product_hook] End updating product on Square");
		}
	} else {
		update_post_meta($post_id, 'is_square_sync', 0);  
	}
}

/*
 * Deleting product 
 */
function woo_square_delete_product($post_id) {
    
    //Avoid auto save from calling Square APIs.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    $product_square_id = get_post_meta($post_id, 'square_id', true);
    $product= get_post($post_id);
    if ($product->post_type == "product" && !empty($product_square_id)) {
        
        Helpers::debug_log('info',"[delete_product_hook] Start deleting product {$post_id} [square:{$product_square_id}] from Square");

        global $wpdb;

        $wpdb->insert($wpdb->prefix.WOO_SQUARE_TABLE_DELETED_DATA,
                [
                    'square_id'  => $product_square_id,
                    'target_id'  => $post_id,
                    'target_type'=> Helpers::TARGET_TYPE_PRODUCT,
                    'name'       => $product->post_title
                ]
        );
                
        if(!get_option('woo_square_access_token_free')){
            return;
        }

        $square = new Square(get_option('woo_square_access_token_free'),get_option('woo_square_location_id_free'));
        $squareSynchronizer = new WooToSquareSynchronizer($square);       
        $result = $squareSynchronizer->deleteProductOrGet($product_square_id,"DELETE");
        
        //delete product from plugin delete table
        if($result===TRUE){
            $wpdb->delete($wpdb->prefix.WOO_SQUARE_TABLE_DELETED_DATA,
                ['square_id'=> $product_square_id ]
            );
            Helpers::debug_log('info',"[delete_product_hook] Product {$post_id} deleted successfully from Square");
        }
        Helpers::debug_log('info',"[delete_product_hook] End deleting product {$post_id} [square:{$product_square_id}] from Square");
    }
}

/*
 * Adding new Category
 */
function woo_square_add_category($category_id) {
    
    //Avoid auto save from calling Square APIs.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $category = get_term_by('id', $category_id, 'product_cat');
    update_option("is_square_sync_{$category_id}", 0);
    Helpers::debug_log('info',"[add_category_hook] Start adding category to Square: {$category_id}");
   
    if(!get_option('woo_square_access_token_free')){
        return;
    }
    
    $square = new Square(get_option('woo_square_access_token_free'),get_option('woo_square_location_id_free'));
    
    $squareSynchronizer = new WooToSquareSynchronizer($square);
    $result = $squareSynchronizer->addCategory($category);
    
    if( $result===TRUE ){
        update_option("is_square_sync_{$category_id}", 1);
    }
    Helpers::debug_log('info',"[add_category_hook] End adding category {$category_id} to Square");
}

/*
 * Edit Category
 */
function woo_square_edit_category($category_id) {
    
    //Avoid auto save from calling Square APIs.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
            
    update_option("is_square_sync_{$category_id}", 0);
   
    if(!get_option('woo_square_access_token_free')){
        return;
    }
    $category = get_term_by('id', $category_id, 'product_cat');
    $categorySquareId = get_option('category_square_id_' . $category->term_id);
    Helpers::debug_log('info',"[edit_category_hook] Start updating category on Square: {$category_id} [square:{$categorySquareId}]");

    $square = new Square(get_option('woo_square_access_token_free'),get_option('woo_square_location_id_free'));
    $squareSynchronizer = new WooToSquareSynchronizer($square);
    
    //add category if not already linked to square, else update
    if( empty($categorySquareId )){
        $result = $squareSynchronizer->addCategory($category);
    }else{
        $result = $squareSynchronizer->editCategory($category,$categorySquareId);
    }
    
    if( $result===TRUE ){
        update_option("is_square_sync_{$category_id}", 1);
        Helpers::debug_log('info',"[edit_category_hook] category {$category_id} updated successfully");
    }
    Helpers::debug_log('info',"[edit_category_hook] End updating category on square: {$category_id} [square:{$categorySquareId}]");
}

/*
 * Delete Category ( called after the category is deleted )
 */
function woo_square_delete_category($category_id,$term_taxonomy_id, $deleted_category) {
   
    //Avoid auto save from calling Square APIs.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    $category_square_id = get_option('category_square_id_' . $category_id);
    
    //delete category options
    delete_option( "is_square_sync_{$category_id}" );
    delete_option( "category_square_id_{$category_id}" );
    
    //no need to call square
    if(empty($category_square_id)){
        return;
    }

    Helpers::debug_log('info',"[delete_category_hook] Start deleting category {$category_id} [square:{$category_square_id}] from Square");
    global $wpdb;

    $wpdb->insert($wpdb->prefix.WOO_SQUARE_TABLE_DELETED_DATA,
            [
                'square_id'  => $category_square_id,
                'target_id'  => $category_id,
                'target_type'=> Helpers::TARGET_TYPE_CATEGORY,
                'name'       => $deleted_category->name
            ]
    );

    if(!get_option('woo_square_access_token_free')){
        return;
    }

    $square = new Square(get_option('woo_square_access_token_free'),get_option('woo_square_location_id_free'));
    $squareSynchronizer = new WooToSquareSynchronizer($square); 
    $result = $squareSynchronizer->deleteCategory($category_square_id);
    
    //delete product from plugin delete table
    if($result===TRUE){
        $wpdb->delete($wpdb->prefix.WOO_SQUARE_TABLE_DELETED_DATA,
            ['square_id'=> $category_square_id ]
        );
        Helpers::debug_log('info',"[delete_category_hook] Category {$category_id} deleted successfully from Square");

    }
    Helpers::debug_log('info',"[delete_category_hook] End deleting category {$category_id} [square:{$category_square_id}] from Square");
}



/*
 * Create Refund
 */

function woo_square_create_refund($order_id, $refund_id) {
    if(!get_option('woo_square_access_token')){
        return;
    }
    //Avoid auto save from calling Square APIs.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (get_post_meta($order_id, 'square_payment_id', true)) {
		
			$order = wc_get_order( $order_id );
		
			$square = new Square(get_option('woo_square_access_token_free'),get_option('woo_square_location_id_free'));
			$square->refund($order_id, $refund_id);
		
		// Get an instance of the order object
    
    // Iterating though each order items
    foreach ( $order->get_items() as $item_id => $item_values ) {

        // Item quantity
        $item_qty = $item_values['qty'];

        // getting the product ID (Simple and variable products)
        $product_id = $item_values['variation_id'];
        if( $product_id == 0 || empty($product_id) ) $product_id = $item_values['product_id'];

        // Get an instance of the product object
        $product = wc_get_product( $product_id );

        // Get the stock quantity of the product
        $product_stock = $product->get_stock_quantity();

        // Increase back the stock quantity
		
		
        wc_update_product_stock( $product, $item_qty, 'increase' );
		
		
    }
		
    }
}


/*
 * update square inventory on complete order 
 */

function woo_square_complete_order($order_id) {
    if(!get_option('woo_square_access_token')){
        return;
    }
    //Avoid auto save from calling Square APIs.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $square = new Square(get_option('woo_square_access_token_free'),get_option('woo_square_location_id_free'));
    $square->completeOrder($order_id);
}



	
function payment_gateway_disable_country( $available_gateways ) {
	global $woocommerce;

 
	if (isset( $available_gateways['square'] ) && !is_ssl()) {
		unset( $available_gateways['square'] );
		
	}
	
	$woocommerce_square_settings = get_option('woocommerce_square_settings');

	if($woocommerce_square_settings['enabled'] != 'yes'){
		unset( $available_gateways['square'] );
	} else if($woocommerce_square_settings['enable_sandbox'] == 'yes'){
		$current_user = wp_get_current_user();
		if(user_can( $current_user, 'administrator' ) != 1){
				// user is an admin
				unset( $available_gateways['square'] );
		} 
	}


	return $available_gateways;
}
add_action( 'admin_notices', 'admin_notice_square_for_ssl' );
function admin_notice_square_for_ssl(){
	
	$woocommerce_square_settings = get_option('woocommerce_square_settings');
	if ($woocommerce_square_settings['enabled'] == 'yes' && !is_ssl() && !wc_checkout_is_https()) {
		$class = 'notice notice-info is-dismissible square-first-notice';
		// $heading = __( 'Good News For Square Users', 'woosquare' );
		$message = __( 'Square is enabled, but a SSL certificate is not detected. Your checkout may not be secure! Please ensure your server has a valid <a href="https://en.wikipedia.org/wiki/Transport_Layer_Security" target="_blank">SSL certificate</a>' );

		// printf( '<div data-dismissible="notice-one-forever-woosquare" class="%1$s"><h2>%2$s</h2><p>%3$s</p></div>', esc_attr( $class ), esc_html( $heading ) ,  $message  ); 
	
		// printf( __( '<div data-dismissible="notice-one-forever-woosquare" class="%1$s">Square is enabled, but a SSL certificate is not detected. Your checkout may not be secure! Please ensure your server has a valid <a href="%1$s" target="_blank">SSL certificate</a></div>', 'woocommerce-square' ), esc_attr( $class ), 'https://en.wikipedia.org/wiki/Transport_Layer_Security' ); 
		printf( __( '<div data-dismissible="notice-one-forever-woosquare" class="%s"><p>%s</p></div>', 'woocommerce-square' ), esc_attr( $class ),$message); 
	}
}
add_filter( 'woocommerce_available_payment_gateways', 'payment_gateway_disable_country' );	
}