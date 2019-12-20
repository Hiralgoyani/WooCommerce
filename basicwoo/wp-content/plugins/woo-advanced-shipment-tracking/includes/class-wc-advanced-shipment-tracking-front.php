<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Front {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	*/
    public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix."woo_shippment_provider";
		if( is_multisite() ){
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if ( is_plugin_active_for_network( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' ) ) {
				$main_blog_prefix = $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE);			
				$this->table = $main_blog_prefix."woo_shippment_provider";	
			} else{
				$this->table = $wpdb->prefix."woo_shippment_provider";
			}			
		} else{
			$this->table = $wpdb->prefix."woo_shippment_provider";	
		}
		
		$this->init();	
    }
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Actions
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init(){
		add_shortcode( 'wcast-track-order', array( $this, 'woo_track_order_function') );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'front_styles' ));		
		
		add_action( 'wp_ajax_nopriv_get_tracking_info', array( $this, 'get_tracking_info_fun') );
		add_action( 'wp_ajax_get_tracking_info', array( $this, 'get_tracking_info_fun') );
		
	}	
			
	/**
	 * Include front js and css
	*/
	public function front_styles(){		
		wp_enqueue_script( 'front-js', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/front.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );
		wp_localize_script( 'front-js', 'zorem_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		
		wp_enqueue_style( 'front_style',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/front.css', array(), wc_advanced_shipment_tracking()->version );				
	}
	
	public function woo_track_order_function(){
		
		global $wpdb;
		$wc_ast_api_key = get_option('wc_ast_api_key');	
		$primary_color = get_option('wc_ast_select_primary_color');
		$success_color = get_option('wc_ast_select_success_color');
		$warning_color = get_option('wc_ast_select_warning_color');
		$border_color = get_option('wc_ast_select_border_color');
		$hide_tracking_events = get_option('wc_ast_hide_tracking_events');	
		?>
		<style>
		
		<?php if($primary_color){ ?>
		.bg-secondary{
			background-color:<?php echo $primary_color; ?> !important;
		}
		.tracker-progress-bar-with-dots .secondary .dot {
			border-color: <?php echo $primary_color; ?>;
		}
		.text-secondary{
			color: <?php echo $primary_color; ?> !important;
		}
		.progress-bar.bg-secondary:before{
			background-color: <?php echo $primary_color; ?>;
		}
		.tracking-number{
			color: <?php echo $primary_color; ?> !important;
		}
		.view_table_rows,.hide_table_rows{
			color: <?php echo $primary_color; ?> !important;
		}
		<?php } ?>	
		<?php if($border_color){ ?>
		.col.tracking-detail{
			border: 1px solid <?php echo $border_color; ?>;
		}
		<?php }	 ?>
		</style>
		<?php 
		if(!$wc_ast_api_key){
			return;
		}
		if(isset($_GET['order_id']) &&  isset($_GET['order_key'])){
			
			$order_id = $_GET['order_id'];
			$order = wc_get_order( $order_id );
			$order_key = $order->get_order_key();
		
			if($order_key != $_GET['order_key']){
				return;
			}
			
			if(empty($order)){
				return;
			}
			
			if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
				$tracking_items = get_post_meta( $order_id, '_wc_shipment_tracking_items', true );			
			} else {				
				$tracking_items = $order->get_meta( '_wc_shipment_tracking_items', true );			
			}
		
			if(!$tracking_items){
				unset($order_id);
			}			
		}
	
	?>
	
		<?php 	
		if(!isset($order_id)){
		ob_start();		
		?>
			<div class="track-order-section">
				<form method="post" class="order_track_form">			
					<p><?php echo apply_filters( 'ast_tracking_page_front_text', __( 'To track your order please enter your Order ID in the box below and press the "Track" button. This was given to you on your receipt and in the confirmation email you should have received.', 'woo-advanced-shipment-tracking' ) ); ?></p>
					<p class="form-row form-row-first"><label for="order_id"><?php echo apply_filters( 'ast_tracking_page_front_order_label', __( 'Order ID', 'woocommerce' ) ); ?></label> <input class="input-text" type="text" name="order_id" id="order_id" value="" placeholder="<?php _e( 'Found in your order confirmation email.', 'woo-advanced-shipment-tracking' ); ?>"></p>
					<p class="form-row form-row-last"><label for="order_email"><?php echo apply_filters( 'ast_tracking_page_front_order_email_label', __( 'Order Email', 'woo-advanced-shipment-tracking' ) ); ?></label> <input class="input-text" type="text" name="order_email" id="order_email" value="" placeholder="<?php _e( 'Found in your order confirmation email.', 'woo-advanced-shipment-tracking' ); ?>"></p>				
					<div class="clear"></div>
					<input type="hidden" name="action" value="get_tracking_info">
					<p class="form-row"><button type="submit" class="button" name="track" value="Track"><?php echo apply_filters( 'ast_tracking_page_front_track_label', __( 'Track', 'woo-advanced-shipment-tracking' ) ); ?></button></p>
					<div class="track_fail_msg" style="display:none;color: red;"></div>	
				</form>
			</div>
		<?php 
		
		$form = ob_get_clean();	
		return $form;
		
		} else{
			ob_start();	
			
			$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
			$order_id = $wast->get_formated_order_id($order_id);						
		
			$num = 1;
			$total_trackings = sizeof($tracking_items);	
		
		foreach($tracking_items as $item){
		
			$tracking_number = $item['tracking_number'];
			$trackship_url = 'https://trackship.info';
			$tracking_provider = $item['tracking_provider'];
			$results = $wpdb->get_row( "SELECT * FROM {$this->table} WHERE ts_slug= '{$tracking_provider}'");
			$tracking_provider = $results->provider_name;
		
			/*** Update in 2.4.1 
			* Change URL
			* Add User Key
			***/
			$url = $trackship_url.'/wp-json/tracking/get_tracking_info';		
			$args['body'] = array(
				'tracking_number' => $tracking_number,
				'order_id' => $order_id,
				'domain' => get_home_url(),
				'user_key' => $wc_ast_api_key,
			);	
			$response = wp_remote_post( $url, $args );
			$data = $response['body'];				
			$decoded_data = json_decode($data);
			
			$tracker = new \stdClass();
			$tracker->ep_status = '';
			if(!empty($decoded_data)){
				$tracker = $decoded_data[0];
			}
			
			$tracking_detail_org = '';	
			$trackind_detail_by_status_rev = '';
			
			if(isset($tracker->tracking_detail) && $tracker->tracking_detail != 'null'){						
				$tracking_detail_org = json_decode($tracker->tracking_detail);						
				$trackind_detail_by_status_rev = array_reverse($tracking_detail_org);	
			}	
			//echo '<pre>';print_r($decoded_data);echo '</pre>';	
			//echo '<pre>';print_r($tracking_detail_org);echo '</pre>';	
		
		if(!empty($decoded_data)){	
		
			if($tracker->est_delivery_date){	
				$unixTimestamp = strtotime($tracker->est_delivery_date);				
				$day = date("l", $unixTimestamp);
			}
				
			if($tracker->ep_status == "unknown"){ $state0_class = 'unknown'; } else{ $state0_class = 'pre_transit'; }		
		
			if($tracker->ep_status == "return_to_sender" ){ 
				$state2_class = 'return_to_sender'; 
			} elseif($tracker->ep_status == "failure"){
				$state2_class = 'failure';
			} elseif($tracker->ep_status == "available_for_pickup"){
				$state2_class = 'available_for_pickup';
			} else{
				$state2_class = 'out_for_delivery';
			}	
		?>
		
			<div class="tracking-detail col">			
				<?php if($total_trackings > 1 ){ ?>
				<p class="shipment_heading"><?php 				
				echo sprintf(__("Shipment - %s (out of %s)", 'woo-advanced-shipment-tracking'), $num , $total_trackings); ?></p>
				<?php } ?>
				<div class="tracking-header">
					<div class="col-md col-md-6">
						<?php _e( 'Order', 'woocommerce' ); ?>: <span class="tracking-number">#<?php echo apply_filters( 'ast_order_number_filter', $order_id); ?></span><br/>
						<?php echo apply_filters( 'ast_provider_title', $tracking_provider); ?>: <span class="tracking-number"><?php echo $tracker->tracking_code; ?></span>
						<h1 class="shipment_status_heading <?php if($tracker->ep_status == "delivered") { echo 'text-success'; } elseif($tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure") { echo 'text-success'; } else{ echo 'text-secondary'; } ?>"><?php echo apply_filters("trackship_status_filter",$tracker->ep_status);?></h1>
					</div>
					<div class="col-md col-md-6">												
						<?php 
						if($tracker->est_delivery_date){
						?>
							<div class="text-muted text-right">
							<?php _e( 'Estimated Delivery Date: ', 'woo-advanced-shipment-tracking' ); ?><span class="tracking-number"><?php echo $day; ?>, <?php echo  date('M d', strtotime($tracker->est_delivery_date)); ?></span>
							</div>
						<?php } else{ ?>
							<div class="text-muted text-right">
								<?php _e( 'Estimated Delivery Date: ', 'woo-advanced-shipment-tracking' ); ?><span class="tracking-number">N/A</span>
							</div>	
						<?php } ?>
					</div>
				</div>
				<?php 				
				if($tracker->ep_status == 'pending_trackship' || $tracker->ep_status == 'INVALID_TRACKING_NUM' || $tracker->ep_status == 'carrier_unsupported' || $tracker->ep_status == 'invalid_user_key' || $tracker->ep_status == 'wrong_shipping_provider' || $tracker->ep_status == 'deleted' || $tracker->ep_status == 'pending'){
					
				}
				elseif(isset($tracker->ep_status)){	?>
				<div class="status-section desktop-section">
					<div class="tracker-progress-bar tracker-progress-bar-with-dots">
						<div class="progress">
							<div class="progress-bar"></div>
						</div>
						<div style="background-color: transparent;" class="<?php if($tracker->ep_status == "delivered") { echo 'success'; } elseif($tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure" || $tracker->ep_status == "unknown") { echo 'warning'; } else{ echo 'secondary';} ?>">
						<span class="dot state-0 <?php echo $state0_class?> <?php if($tracker->ep_status =="pre_transit" || $tracker->ep_status =="unknown"){ echo ' current-state'; } else{ echo 'past-state';} ?>"></span>
						<span class="state-label <?php if($tracker->ep_status =="pre_transit" || $tracker->ep_status =="unknown"){ echo 'current-state'; } else{ echo 'past-state';} ?>">
						<?php 
							if($tracker->ep_status == "unknown"){
								echo apply_filters("trackship_status_filter",'unknown');								
							} else{
								echo apply_filters("trackship_status_filter",'pre_transit');	
							}	
						?>						
						</span>
						             
						<span class="dot state-1 in_transit <?php if($tracker->ep_status == "in_transit"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown"){ echo 'future-state'; } else{ echo 'past-state'; } ?>"></span>
						<span class="state-label state-1 <?php if($tracker->ep_status == "in_transit"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown"){ echo 'future-state'; } else{ echo 'past-state'; } ?>">
						<?php echo apply_filters("trackship_status_filter",'in_transit'); ?>						
						</span>
											
						<span class="dot state-2 <?php echo $state2_class; if($tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "failure" || $tracker->ep_status == "return_to_sender"){ echo ' current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit"){ echo ' future-state'; } else{ echo ' past-state'; } ?>"></span>
						<span class="state-label state-2 <?php if($tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "failure" || $tracker->ep_status == "return_to_sender"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit"){ echo 'future-state'; } else{ echo 'past-state'; } ?>">
						<?php
							if($tracker->ep_status == "return_to_sender"){
								echo apply_filters("trackship_status_filter",'return_to_sender');								
							} elseif($tracker->ep_status == "failure"){
								echo apply_filters("trackship_status_filter",'failure');								
							} else{
								echo apply_filters("trackship_status_filter",'out_for_delivery');
							}
						?>						
						</span>
												
						<span class="dot state-3 delivered <?php if($tracker->ep_status == "delivered"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit" || $tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure"){ echo 'future-state'; }?>"></span>
						<span class="state-label state-3 <?php if($tracker->ep_status == "delivered"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit" || $tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure"){ echo 'future-state'; }?>">
						<?php echo apply_filters("trackship_status_filter",'delivered'); ?>
						</span>
						</div>
					</div>
				</div>

				<div class="status-section mobile-section">
					<div class="tracker-progress-bar tracker-progress-bar-with-dots">
						<div class="progress">
							<div class="progress-bar <?php if($tracker->ep_status == "delivered") { echo 'bg-success'; } elseif($tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure"){ echo 'bg-warning'; } else{ echo 'bg-secondary';} ?>"></div>
						</div>
						<div style="background-color: transparent;" class="<?php if($tracker->ep_status == "delivered") { echo 'success'; } elseif($tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure" || $tracker->ep_status == "unknown") { echo 'warning'; } else{ echo 'secondary';} ?>">
						
						<div class="dot-div">							
							<span class="dot state-0 <?php echo $state0_class?> <?php if($tracker->ep_status =="pre_transit" || $tracker->ep_status =="unknown"){ echo ' current-state'; } else{ echo 'past-state';} ?>"></span>
							<span class="state-label <?php if($tracker->ep_status =="pre_transit" || $tracker->ep_status =="unknown"){ echo 'current-state'; } else{ echo 'past-state';} ?>">
							<?php 
								if($tracker->ep_status == "unknown"){
									echo apply_filters("trackship_status_filter",'unknown');								
								} else{
									echo apply_filters("trackship_status_filter",'pre_transit');	
								}	
							?>						
							</span>
						</div>

						<div class="dot-div">	
							<span class="dot state-1 in_transit <?php if($tracker->ep_status == "in_transit"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown"){ echo 'future-state'; } else{ echo 'past-state'; } ?>"></span>
							<span class="state-label state-1 <?php if($tracker->ep_status == "in_transit"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown"){ echo 'future-state'; } else{ echo 'past-state'; } ?>">
								<?php echo apply_filters("trackship_status_filter",'in_transit'); ?>						
							</span>
						</div>
						
						<div class="dot-div">
							<span class="dot state-2 <?php echo $state2_class; if($tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "failure" || $tracker->ep_status == "return_to_sender"){ echo ' current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit"){ echo ' future-state'; } else{ echo ' past-state'; } ?>"></span>
							<span class="state-label state-2 <?php if($tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "failure" || $tracker->ep_status == "return_to_sender"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit"){ echo 'future-state'; } else{ echo ' past-state'; } ?>">
								<?php
									if($tracker->ep_status == "return_to_sender"){
										echo apply_filters("trackship_status_filter",'return_to_sender');								
									} elseif($tracker->ep_status == "failure"){
										echo apply_filters("trackship_status_filter",'failure');								
									} else{
										echo apply_filters("trackship_status_filter",'out_for_delivery');
									}
								?>						
							</span>
						</div>
						
						<div class="dot-div">	
							<span class="dot state-3 delivered <?php if($tracker->ep_status == "delivered"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit" || $tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure"){ echo 'future-state'; }?>"></span>
							<span class="state-label state-3 <?php if($tracker->ep_status == "delivered"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit" || $tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure"){ echo 'future-state'; }?>">
							<?php echo apply_filters("trackship_status_filter",'delivered'); ?>
							</span>
						</div>
						
						</div>
					</div>
				</div>	
		<?php }  ?>		
			<?php if( !empty($trackind_detail_by_status_rev) && $hide_tracking_events != 1  ){ ?>				
				<div class="shipment_progress_div">
					<div class="shipment_progress_heading_div">	               				
						<h4 class="tracking-number h4-heading" style=""><?php _e( 'Tracking Details', 'woo-advanced-shipment-tracking' ); ?></h4>					
					</div>				
					<table class="tracking-table">					
						<tbody>
						<?php 
						$i=0;
						foreach($trackind_detail_by_status_rev as $key=>$status_detail){ ?> 
							<tr>
								<td><?php echo date_i18n( get_option( 'date_format' ), strtotime($status_detail->datetime) ); ?>
								<?php echo date_i18n( get_option( 'time_format' ), strtotime($status_detail->datetime) ); ?></td>
								<td><?php echo apply_filters("trackship_status_filter",$status_detail->status);?></td>
								<td><?php echo $status_detail->message; ?></td>
							</tr>
						<?php }
						?>
						</tbody>
					</table>
					<?php if(count($trackind_detail_by_status_rev) > 2){ ?>
					
					<a class="view_table_rows" href="javaScript:void(0);"><?php _e( 'view more', 'woo-advanced-shipment-tracking' ); ?></a>
					<a class="hide_table_rows" href="javaScript:void(0);"><?php _e( 'view less', 'woo-advanced-shipment-tracking' ); ?></a>
					<?php } ?>				
				</div>	
			<?php } ?>	
			</div>		
		<?php } else{ ?>
			<div class="tracking-detail col">
				<h1 class="shipment_status_heading text-secondary text-center"><?php _e( 'Tracking&nbsp;#&nbsp;'.$tracking_number, 'woo-advanced-shipment-tracking' ); ?></h1>
				<h3 class="text-center"><?php _e( 'Tracking details not found in TrackShip', 'woo-advanced-shipment-tracking' ); ?></h3>
			</div>
		<?php } 
		$num++;
		}		
		
		$remove_trackship_branding =  get_option('wc_ast_remove_trackship_branding');
		
		if($remove_trackship_branding != 1){ ?> 
			<div class="trackship_branding">
				<p>Shipment Tracking info by <a href="https://trackship.info" title="TrackShip" target="blank">TrackShip</a></p>
			</div>
		<?php }
		
		$form = ob_get_clean();	
		return $form;		
		} 
	}
	
	public function get_tracking_info_fun(){
		
		global $wpdb;
		$wc_ast_api_key = get_option('wc_ast_api_key');	
		$hide_tracking_events = get_option('wc_ast_hide_tracking_events');		
		
		if(!$wc_ast_api_key){
			return;
		}
		
		$order_id = $_POST['order_id'];		
		$email = $_POST['order_email'];
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $wast->get_formated_order_id($order_id);		
		
		$order = wc_get_order( $order_id );
		
		if(empty($order)){
			echo '';
			exit;
		}
				
		$order_email = $order->get_billing_email();
		
		if(strtolower($order_email) != strtolower($email)){
			echo '';
			exit;
		}
		
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$tracking_items = get_post_meta( $order_id, '_wc_shipment_tracking_items', true );			
		} else {			
			$tracking_items = $order->get_meta( '_wc_shipment_tracking_items', true );			
		} 
		
		if(!$tracking_items){
			echo 'tracking_items_not_found';
			exit;
		}
		
		$num = 1;
		$total_trackings = sizeof($tracking_items);	
		
		foreach($tracking_items as $item){
			
			$tracking_number = $item['tracking_number'];
			$trackship_url = 'https://trackship.info';
			$tracking_provider = $item['tracking_provider'];
			$results = $wpdb->get_row( "SELECT * FROM {$this->table} WHERE ts_slug= '{$tracking_provider}'");
			$tracking_provider = $results->provider_name;
		
			/*** Update in 2.4.1 
			* Change URL
			* Add User Key
			***/
			$url = $trackship_url.'/wp-json/tracking/get_tracking_info';		
			$args['body'] = array(
				'tracking_number' => $tracking_number,
				'order_id' => $order_id,
				'domain' => get_home_url(),
				'user_key' => $wc_ast_api_key,
			);	
			$response = wp_remote_post( $url, $args );
			$data = $response['body'];				
			$decoded_data = json_decode($data);
			
			$tracker = new \stdClass();
			$tracker->ep_status = '';
			if(!empty($decoded_data)){
				$tracker = $decoded_data[0];
			}
			
			$tracking_detail_org = '';	
			$trackind_detail_by_status_rev = '';
			
			if(isset($tracker->tracking_detail) && $tracker->tracking_detail != 'null'){						
				$tracking_detail_org = json_decode($tracker->tracking_detail);						
				$trackind_detail_by_status_rev = array_reverse($tracking_detail_org);	
			}				
		
		if(!empty($decoded_data)){	
		
			if($tracker->est_delivery_date){	
				$unixTimestamp = strtotime($tracker->est_delivery_date);				
				$day = date("l", $unixTimestamp);
			}
				
			if($tracker->ep_status == "unknown"){ $state0_class = 'unknown'; } else{ $state0_class = 'pre_transit'; }		
		
			if($tracker->ep_status == "return_to_sender" ){ 
				$state2_class = 'return_to_sender'; 
			} elseif($tracker->ep_status == "failure"){
				$state2_class = 'failure';
			} elseif($tracker->ep_status == "available_for_pickup"){
				$state2_class = 'available_for_pickup';
			} else{
				$state2_class = 'out_for_delivery';
			}	
		?>
		
			<div class="tracking-detail col">			
				<?php if($total_trackings > 1 ){ ?>
				<p class="shipment_heading"><?php 				
				echo sprintf(__("Shipment - %s (out of %s)", 'woo-advanced-shipment-tracking'), $num , $total_trackings); ?></p>
				<?php } ?>
				<div class="tracking-header">
					<div class="col-md col-md-6">
						<?php _e( 'Order', 'woocommerce' ); ?>: <span class="tracking-number">#<?php echo apply_filters( 'ast_order_number_filter', $order_id); ?></span><br/>
						<?php echo apply_filters( 'ast_provider_title', $tracking_provider); ?>: <span class="tracking-number"><?php echo $tracker->tracking_code; ?></span>
						<h1 class="shipment_status_heading <?php if($tracker->ep_status == "delivered") { echo 'text-success'; } elseif($tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure") { echo 'text-success'; } else{ echo 'text-secondary'; } ?>"><?php echo apply_filters("trackship_status_filter",$tracker->ep_status);?></h1>
					</div>
					<div class="col-md col-md-6">												
						<?php 
						if($tracker->est_delivery_date){
						?>
							<div class="text-muted text-right">
							<?php _e( 'Estimated Delivery Date: ', 'woo-advanced-shipment-tracking' ); ?><span class="tracking-number"><?php echo $day; ?>, <?php echo  date('M d', strtotime($tracker->est_delivery_date)); ?></span>
							</div>
						<?php } else{ ?>
							<div class="text-muted text-right">
								<?php _e( 'Estimated Delivery Date: ', 'woo-advanced-shipment-tracking' ); ?><span class="tracking-number">N/A</span>
							</div>	
						<?php } ?>
					</div>
				</div>
				<?php 				
				if($tracker->ep_status == 'pending_trackship' || $tracker->ep_status == 'INVALID_TRACKING_NUM' || $tracker->ep_status == 'carrier_unsupported' || $tracker->ep_status == 'invalid_user_key' || $tracker->ep_status == 'wrong_shipping_provider' || $tracker->ep_status == 'deleted' || $tracker->ep_status == 'pending'){
					
				}
				elseif(isset($tracker->ep_status)){	?>
				<div class="status-section desktop-section">
					<div class="tracker-progress-bar tracker-progress-bar-with-dots">
						<div class="progress">
							<div class="progress-bar"></div>
						</div>
						<div style="background-color: transparent;" class="<?php if($tracker->ep_status == "delivered") { echo 'success'; } elseif($tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure" || $tracker->ep_status == "unknown") { echo 'warning'; } else{ echo 'secondary';} ?>">
						<span class="dot state-0 <?php echo $state0_class?> <?php if($tracker->ep_status =="pre_transit" || $tracker->ep_status =="unknown"){ echo ' current-state'; } else{ echo 'past-state';} ?>"></span>
						<span class="state-label <?php if($tracker->ep_status =="pre_transit" || $tracker->ep_status =="unknown"){ echo 'current-state'; } else{ echo 'past-state';} ?>">
						<?php 
							if($tracker->ep_status == "unknown"){
								echo apply_filters("trackship_status_filter",'unknown');								
							} else{
								echo apply_filters("trackship_status_filter",'pre_transit');	
							}	
						?>						
						</span>
						             
						<span class="dot state-1 in_transit <?php if($tracker->ep_status == "in_transit"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown"){ echo 'future-state'; } else{ echo 'past-state'; } ?>"></span>
						<span class="state-label state-1 <?php if($tracker->ep_status == "in_transit"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown"){ echo 'future-state'; } else{ echo 'past-state'; } ?>">
						<?php echo apply_filters("trackship_status_filter",'in_transit'); ?>						
						</span>
											
						<span class="dot state-2 <?php echo $state2_class; if($tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "failure" || $tracker->ep_status == "return_to_sender"){ echo ' current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit"){ echo ' future-state'; } else{ echo ' past-state'; } ?>"></span>
						<span class="state-label state-2 <?php if($tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "failure" || $tracker->ep_status == "return_to_sender"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit"){ echo 'future-state'; } else{ echo 'past-state'; } ?>">
						<?php
							if($tracker->ep_status == "return_to_sender"){
								echo apply_filters("trackship_status_filter",'return_to_sender');								
							} elseif($tracker->ep_status == "failure"){
								echo apply_filters("trackship_status_filter",'failure');								
							} else{
								echo apply_filters("trackship_status_filter",'out_for_delivery');
							}
						?>						
						</span>
												
						<span class="dot state-3 delivered <?php if($tracker->ep_status == "delivered"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit" || $tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure"){ echo 'future-state'; }?>"></span>
						<span class="state-label state-3 <?php if($tracker->ep_status == "delivered"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit" || $tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure"){ echo 'future-state'; }?>">
						<?php echo apply_filters("trackship_status_filter",'delivered'); ?>
						</span>
						</div>
					</div>
				</div>

				<div class="status-section mobile-section">
					<div class="tracker-progress-bar tracker-progress-bar-with-dots">
						<div class="progress">
							<div class="progress-bar <?php if($tracker->ep_status == "delivered") { echo 'bg-success'; } elseif($tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure"){ echo 'bg-warning'; } else{ echo 'bg-secondary';} ?>"></div>
						</div>
						<div style="background-color: transparent;" class="<?php if($tracker->ep_status == "delivered") { echo 'success'; } elseif($tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure" || $tracker->ep_status == "unknown") { echo 'warning'; } else{ echo 'secondary';} ?>">
						
						<div class="dot-div">							
							<span class="dot state-0 <?php echo $state0_class?> <?php if($tracker->ep_status =="pre_transit" || $tracker->ep_status =="unknown"){ echo ' current-state'; } else{ echo 'past-state';} ?>"></span>
							<span class="state-label <?php if($tracker->ep_status =="pre_transit" || $tracker->ep_status =="unknown"){ echo 'current-state'; } else{ echo 'past-state';} ?>">
							<?php 
								if($tracker->ep_status == "unknown"){
									echo apply_filters("trackship_status_filter",'unknown');								
								} else{
									echo apply_filters("trackship_status_filter",'pre_transit');	
								}	
							?>						
							</span>
						</div>

						<div class="dot-div">	
							<span class="dot state-1 in_transit <?php if($tracker->ep_status == "in_transit"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown"){ echo 'future-state'; } else{ echo 'past-state'; } ?>"></span>
							<span class="state-label state-1 <?php if($tracker->ep_status == "in_transit"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown"){ echo 'future-state'; } else{ echo 'past-state'; } ?>">
								<?php echo apply_filters("trackship_status_filter",'in_transit'); ?>						
							</span>
						</div>
						
						<div class="dot-div">
							<span class="dot state-2 <?php echo $state2_class; if($tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "failure" || $tracker->ep_status == "return_to_sender"){ echo ' current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit"){ echo ' future-state'; } else{ echo ' past-state'; } ?>"></span>
							<span class="state-label state-2 <?php if($tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "failure" || $tracker->ep_status == "return_to_sender"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit"){ echo 'future-state'; } else{ echo ' past-state'; } ?>">
								<?php
									if($tracker->ep_status == "return_to_sender"){
										echo apply_filters("trackship_status_filter",'return_to_sender');								
									} elseif($tracker->ep_status == "failure"){
										echo apply_filters("trackship_status_filter",'failure');								
									} else{
										echo apply_filters("trackship_status_filter",'out_for_delivery');
									}
								?>						
							</span>
						</div>
						
						<div class="dot-div">	
							<span class="dot state-3 delivered <?php if($tracker->ep_status == "delivered"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit" || $tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure"){ echo 'future-state'; }?>"></span>
							<span class="state-label state-3 <?php if($tracker->ep_status == "delivered"){ echo 'current-state'; } elseif($tracker->ep_status == "pre_transit" || $tracker->ep_status =="unknown" || $tracker->ep_status == "in_transit" || $tracker->ep_status == "out_for_delivery" || $tracker->ep_status == "available_for_pickup" || $tracker->ep_status == "return_to_sender" || $tracker->ep_status == "failure"){ echo 'future-state'; }?>">
							<?php echo apply_filters("trackship_status_filter",'delivered'); ?>
							</span>
						</div>
						
						</div>
					</div>
				</div>	
		<?php }  ?>		
			<?php if( !empty($trackind_detail_by_status_rev) && $hide_tracking_events != 1  ){ ?>				
				<div class="shipment_progress_div">
					<div class="shipment_progress_heading_div">	               				
						<h4 class="tracking-number h4-heading" style=""><?php _e( 'Tracking Details', 'woo-advanced-shipment-tracking' ); ?></h4>					
					</div>				
					<table class="tracking-table">					
						<tbody>
						<?php 
						$i=0;
						foreach($trackind_detail_by_status_rev as $key=>$status_detail){ ?> 
							<tr>
								<td><?php echo date_i18n( get_option( 'date_format' ), strtotime($status_detail->datetime) ); ?>
								<?php echo date_i18n( get_option( 'time_format' ), strtotime($status_detail->datetime) ); ?></td>
								<td><?php echo apply_filters("trackship_status_filter",$status_detail->status);?></td>
								<td><?php echo $status_detail->message; ?></td>
							</tr>
						<?php }
						?>
						</tbody>
					</table>
					<?php if(count($trackind_detail_by_status_rev) > 2){ ?>
					
					<a class="view_table_rows" href="javaScript:void(0);"><?php _e( 'view more', 'woo-advanced-shipment-tracking' ); ?></a>
					<a class="hide_table_rows" href="javaScript:void(0);"><?php _e( 'view less', 'woo-advanced-shipment-tracking' ); ?></a>
					<?php } ?>				
				</div>	
			<?php } ?>	
			</div>		
		<?php } else{ ?>
			<div class="tracking-detail col">
				<h1 class="shipment_status_heading text-secondary text-center"><?php _e( 'Tracking&nbsp;#&nbsp;'.$tracking_number, 'woo-advanced-shipment-tracking' ); ?></h1>
				<h3 class="text-center"><?php _e( 'Tracking details not found in TrackShip', 'woo-advanced-shipment-tracking' ); ?></h3>
			</div>
		<?php } 
		$num++;	
		}
		
		$remove_trackship_branding =  get_option('wc_ast_remove_trackship_branding');
		
		if($remove_trackship_branding != 1){ ?>
		
		<div class="trackship_branding">
			<p>Shipment Tracking info by <a href="https://trackship.info" title="TrackShip" target="blank">TrackShip</a></p>
		</div>	
		
		<?php }
		
		exit; 
	}
	
	/**
	 * convert string to date
	*/
	public static function convertString ($date) 
    { 
        // convert date and time to seconds 
        $sec = strtotime($date); 
  
        // convert seconds into a specific format 
        $date = date("m/d/Y H:i", $sec); 
  
        // print final date and time 
        return $date; 
    } 
	
	public static function preview_tracking_page(){
		$action = (isset($_REQUEST["action"])?$_REQUEST["action"]:"");
		if($action != 'preview_tracking_page')return;

		get_header();

		$wc_ast_api_key = get_option('wc_ast_api_key');	
		$primary_color = get_option('wc_ast_select_primary_color');	
		$border_color = get_option('wc_ast_select_border_color');
		$hide_tracking_events = get_option('wc_ast_hide_tracking_events');	
		?>
		
		<style>	
			<?php if($primary_color){ ?>
			.bg-secondary{
				background-color:<?php echo $primary_color; ?> !important;
			}
			.tracker-progress-bar-with-dots .secondary .dot {
				border-color: <?php echo $primary_color; ?>;
			}
			.text-secondary{
				color: <?php echo $primary_color; ?> !important;
			}
			.progress-bar.bg-secondary:before{
				background-color: <?php echo $primary_color; ?>;
			}
			.tracking-number{
				color: <?php echo $primary_color; ?> !important;
			}
			.view_table_rows,.hide_table_rows{
				color: <?php echo $primary_color; ?> !important;
			}
			<?php }
			if($border_color){ ?>
			.col.tracking-detail{
				border: 1px solid <?php echo $border_color; ?>;
			}
			<?php }	?>
		</style>		
		
		<div class="tracking-detail col">
			<div class="tracking-header">
				<div class="col-md col-md-6">
					<?php _e( 'Order', 'woocommerce' ); ?>: <span class="tracking-number">#4542</span><br>
					USPS: <span class="tracking-number">9405511899561468285343</span>
					<h1 class="shipment_status_heading text-success">Delivered</h1>
				</div>
				<div class="col-md col-md-6">
					<div class="text-muted text-right">
						<?php _e( 'Estimated Delivery Date', 'woo-advanced-shipment-tracking' ); ?>: <span class="tracking-number">Friday, Jun 28</span>
					</div>
				</div>
			</div>
			<div class="status-section desktop-section">
				<div class="tracker-progress-bar tracker-progress-bar-with-dots">
					<div class="progress">
						<div class="progress-bar bg-success" style=""></div>
					</div>
					<div style="background-color: transparent;" class="success">
						<span class="dot state-0 pre_transit past-state"></span>
						<span class="state-label past-state">
						Pre Transit						
						</span>
						<span class="dot state-1 in_transit past-state"></span>
						<span class="state-label state-1 past-state">
						In Transit						
						</span>
						<span class="dot state-2 out_for_delivery past-state"></span>
						<span class="state-label state-2 past-state">
						Out for delivery						
						</span>
						<span class="dot state-3 delivered current-state"></span>
						<span class="state-label state-3 current-state">
						Delivered						</span>
					</div>
				</div>
			</div>
			<div class="status-section mobile-section">
				<div class="tracker-progress-bar tracker-progress-bar-with-dots">
					<div class="progress">
						<div class="progress-bar bg-success" style=""></div>
					</div>
					<div style="background-color: transparent;" class="success">
						<div class="dot-div">							
						<span class="dot state-0 pre_transit past-state"></span>
						<span class="state-label past-state">
						Pre Transit						
						</span>
						</div>
						<div class="dot-div">	
						<span class="dot state-1 in_transit past-state"></span>
						<span class="state-label state-1 past-state">
						In Transit						
						</span>
						</div>
						<div class="dot-div">
						<span class="dot state-2 out_for_delivery past-state"></span>
						<span class="state-label state-2  past-state">
						Out for delivery						
						</span>
						</div>
						<div class="dot-div">	
						<span class="dot state-3 delivered current-state"></span>
						<span class="state-label state-3 current-state">
						Delivered							</span>
						</div>
					</div>
				</div>
			</div>
		
		<?php if($hide_tracking_events != 1){ ?>
			
			<div class="shipment_progress_div">
				<div class="shipment_progress_heading_div">         
					<h4 class="tracking-number h4-heading"><?php _e( 'Tracking Details', 'woo-advanced-shipment-tracking' ); ?></h4>
				</div>
				<table class="tracking-table">
					<tbody>
						<tr>
						<td>June 28, 2019 1:46 pm</td>
						<td>Delivered</td>
						<td>Delivered, Garage or Other Location at Address</td>
						</tr>
						<tr>
						<td>June 28, 2019 8:31 am</td>
						<td>Out for delivery</td>
						<td>Out for Delivery</td>
						</tr>
						<tr style="display: none;">
						<td>June 28, 2019 8:21 am</td>
						<td>In Transit</td>
						<td>Sorting Complete</td>
						</tr>
					</tbody>
				</table>
				<a class="view_table_rows" href="javaScript:void(0);" style="display: inline;"><?php _e( 'view more', 'woo-advanced-shipment-tracking' ); ?></a>
				<a class="hide_table_rows" href="javaScript:void(0);" style="display: none;"><?php _e( 'view less', 'woo-advanced-shipment-tracking' ); ?></a>
			</div>
			
		<?php } ?>
		
		</div>		
		<?php 
		
		$remove_trackship_branding =  get_option('wc_ast_remove_trackship_branding');
		
		if($remove_trackship_branding != 1){ ?> 
			<div class="trackship_branding">
				<p>Shipment Tracking info by <a href="https://trackship.info" title="TrackShip" target="blank">TrackShip</a></p>
			</div>
		<?php } 			
		get_footer();
		exit;
	}
}