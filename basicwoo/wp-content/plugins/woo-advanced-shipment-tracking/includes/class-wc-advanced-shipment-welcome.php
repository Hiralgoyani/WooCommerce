<?php

/**
 * Class AST_Welcome
 */
class AST_Welcome {

	/**
	 * AST_Welcome constructor.
	 */
	public function __construct() {

		// If we are not in admin or admin ajax, return
		if ( ! is_admin() ) {
			return;
		}
		
		add_action( 'admin_init', array( $this, 'maybe_redirect' ), 9999 );
		add_action( 'admin_menu', array( $this, 'register_welcome_page' ) );
		add_action( 'admin_head', array( $this, 'hide_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'welcome_scripts' ) );
		add_action( 'in_admin_header', array( $this, 'remove_all_admin_notice' ), 1000 );
	}
	
	/**
	 * Register the pages to be used for the Welcome screen.
	 *
	 * These pages will be removed from the Dashboard menu, so they will
	 * not actually show. Sneaky, sneaky.
	 *
	 * @since 1.0.0
	 */
	public function register_welcome_page() {

		// Getting started - shows after installation.
		add_dashboard_page(
			esc_html__( 'Welcome to Advanced Shipment Tracking', 'woo-advanced-shipment-tracking' ),
			esc_html__( 'Welcome to Advanced Shipment Tracking', 'woo-advanced-shipment-tracking' ),
			apply_filters( 'ast_welcome_cap', 'manage_options' ),
			'ast-getting-started',
			array( $this, 'welcome_screen' )
		);
	}
	
	/**
	 * Removed the dashboard pages from the admin menu.
	 *
	 * This means the pages are still available to us, but hidden.
	 *
	 * @since 1.0.0
	 */
	public function hide_menu() {
		remove_submenu_page( 'index.php', 'ast-getting-started' );
	}
	
	/**
	 * Load the welcome screen content.
	 */
	public function welcome_screen() { ?>
		<div class="ast-admin-welcome-page">
			<header class="ast-onboarding-header">
				<!--nav class="ast-header-navigation"><a href="https://www.inearu.com/wp-admin/admin.php?page=ast_settings" class="ast-exit-button"><i class="monstericon-times-circle"></i><span>Exit Setup</span></a></nav-->
				<h1 class="ast-onboarding-wizard-logo"><div class="ast-logo"><div class="ast-bg-img"><img class="plugin-logo" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ast-logo.png"></div></div></h1></header>
			<div class="ast-onboarding-wizard-container">
				<div class="ast-onboarding-wizard-steps">
					<!---->
					<div class="ast-onboarding-wizard-step step-welcome ast-onboarding-wizard-step-active"></div>
					<div class="ast-onboarding-wizard-step-line"></div>
					<div class="ast-onboarding-wizard-step step-shipping"></div>
					<div class="ast-onboarding-wizard-step-line"></div>
					<div class="ast-onboarding-wizard-step step-delivered"></div>
					<div class="ast-onboarding-wizard-step-line"></div>
					<div class="ast-onboarding-wizard-step step-trackship"></div>					
				</div>
			</div>
			<div class="ast-onboarding-wizard-container">
				<div class="woocommerce zorem_admin_layout ast-onboarding-wizard-content">
					<div class="ast-onboarding-step-welcome">
						<header>
							<h2>General Settings</h2>							
						</header>
						<div class="ast-onboarding-wizard-form">
							<div class="ast-separator"></div>
							<form method="post" id="wc_ast_settings_form" action="" enctype="multipart/form-data">		
								<?php 
								$admin = new WC_Advanced_Shipment_Tracking_Admin;
								$admin->get_html( $admin->get_settings_data() ); ?>	
								<div class="submit">								
									<button name="save" class="button-primary ast-save-setup-settings btn_ast2 btn_large" type="button" value="Save changes"><?php _e( 'Save and Continue', 'woo-advanced-shipment-tracking' ); ?></button>
									<div class="spinner"></div>								
									<?php wp_nonce_field( 'wc_ast_settings_form', 'wc_ast_settings_form' );?>
									<input type="hidden" name="action" value="wc_ast_settings_form_update">
								</div>	
							</form>
						</div>
					</div>
					<div class="ast-onboarding-step-shipping">
						<header>
							<h2>Shipping Providers</h2>							
						</header>
						<div class="ast-onboarding-wizard-form">
							<div class="ast-separator"></div>
							<?php 
							$admin = new WC_Advanced_Shipment_Tracking_Admin;
							global $order;
							$WC_Countries = new WC_Countries();
							$countries = $WC_Countries->get_countries();
							
							global $wpdb;
							$woo_shippment_table_name = $admin->table;			
							
							$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $woo_shippment_table_name WHERE display_in_order = 1" );
						
							foreach($default_shippment_providers as $key => $value){			
								$search  = array('(US)', '(UK)');
								$replace = array('', '');
								if($value->shipping_country && $value->shipping_country != 'Global'){
									$country = str_replace($search, $replace, $WC_Countries->countries[$value->shipping_country]);
									$default_shippment_providers[$key]->country = $country;			
								} elseif($value->shipping_country && $value->shipping_country == 'Global'){
									$default_shippment_providers[$key]->country = 'Global';
								}
							}	
							//$admin->get_html( $admin->get_settings_data() );
							require_once( 'views/admin_options_shipping_provider.php' );
							?>	
							<div class="submit">								
								<button name="save" class="button-primary ast-save-setup-providers btn_ast2 btn_large" type="button" value="Save changes"><?php _e( 'Save and Continue', 'woo-advanced-shipment-tracking' ); ?></button>
								<div class="spinner"></div>								
								<?php wp_nonce_field( 'wc_ast_settings_form', 'wc_ast_settings_form' );?>
								<input type="hidden" name="action" value="wc_ast_settings_form_update">
							</div>							
						</div>
					</div>
					<div class="ast-onboarding-step-delivered">
						<header>
							<h2>Delivered Order Status</h2>							
						</header>
						<div class="ast-onboarding-wizard-form">
							<div class="ast-separator"></div>
							<form method="post" id="wc_ast_delivered_settings_form" action="" enctype="multipart/form-data">		
								<?php 
								$admin = new WC_Advanced_Shipment_Tracking_Admin;
								$admin->get_html( $admin->get_delivered_data() );?>	
								<div class="submit">								
									<button name="save" class="button-primary ast-save-setup-delivered btn_ast2 btn_large" type="button" value="Save changes"><?php _e( 'Save and Continue', 'woo-advanced-shipment-tracking' ); ?></button>
									<div class="spinner"></div>								
									<?php wp_nonce_field( 'wc_ast_settings_form', 'wc_ast_settings_form' );?>
									<input type="hidden" name="action" value="wc_ast_settings_form_update">
								</div>	
							</form>
						</div>
					</div>
					<div class="ast-onboarding-step-trackship">
								
						<?php 
						$wc_ast_api_key = get_option('wc_ast_api_key');						
						if($wc_ast_api_key){								
							$url = 'https://my.trackship.info/wp-json/tracking/get_user_plan';								
							$args['body'] = array(
								'user_key' => $wc_ast_api_key,				
							);
							$response = wp_remote_post( $url, $args );
							$plan_data = json_decode($response['body']);				
						?>			
						<header>
							<?php if($wc_ast_api_key){ ?>
								<h2>TrackShip</h2>
							<?php } else{ ?>
								<h2>Connect TrackShip to Your Website</h2>							
							<?php } ?>
						</header>	
						<table class="form-table heading-table">
							<tbody>				
								<tr valign="top">
									<td><h3 style=""><?php _e( 'Connection status', 'woo-advanced-shipment-tracking' ); ?></h3></td>					
								</tr>
							</tbody>
						</table>	
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<td><label><?php _e( 'TrackShip Connection Status', 'woo-advanced-shipment-tracking' ); ?></label></td>
									<td class="forminp">
										<fieldset>
											<a href="https://my.trackship.info/" target="_blank">
												<span class="api_connected"><label><?php _e( 'Connected', 'woo-advanced-shipment-tracking' ); ?></label><span class="dashicons dashicons-yes"></span></span>
											</a>
										</fieldset>						
									</td>					
								</tr>
								<tr valign="top">
									<td><label><?php _e( 'Trackers Balance', 'woo-advanced-shipment-tracking' ); ?></label></td>
									<td class="forminp">
										<fieldset>
											<strong><?php echo get_option('trackers_balance'); ?></strong>
										</fieldset>						
									</td>					
								</tr>
								<tr valign="top">
									<td><label><?php _e( 'Current Plan', 'woo-advanced-shipment-tracking' ); ?></label></td>
									<td class="forminp">
										<fieldset>
											<strong>
												<?php 
													if(isset($plan_data->subscription_plan)){
														echo $plan_data->subscription_plan;
													}
												?>
											</strong>	
										</fieldset>						
									</td>					
								</tr>						
								<tr valign="top">										
									<td colspan="2">
										<?php _e( 'You are now connected with TrackShip! TrackShip makes it effortless to automate your post shipping operations and get tracking and delivery status updates directly in the WooCommerce admin.', 'woo-advanced-shipment-tracking' ); ?>					
									</td>					
								</tr>
								<tr valign="top">										
									<td colspan="2">
										<a href="https://trackship.info/documentation/" class="" style="margin-right: 10px;" target="blank"><?php _e( 'Documentation', 'woo-advanced-shipment-tracking' ); ?></a>
										<a href="https://my.trackship.info/" class="" target="blank"><?php _e( 'TrackShip Dashboard', 'woo-advanced-shipment-tracking' ); ?></a>						
									</td>					
								</tr>
							</tbody>
						</table>						
						
						<?php } else{ ?>
							<div class="section-content trackship_section">
								<div class="trackship-upsell-overlay">
									<div class="trackship-upsell-top">
										<h3><img src="https://trackship.info/wp-content/uploads/2019/08/trackship-400.png" class="trackship_logo"></h3>
										<p class="trackship-upsell-subtitle">TracksShip is a premium shipment tracking API flatform that fully integrates with WooCommerce with the Advanced Shipment Tracking. TrackShip automates the order management workflows, reduces customer inquiries, reduces time spent on customer service, and improves the post-purchase experience and satisfaction of your customers.</p>
										<p class="trackship-upsell-subtitle">You must have account TracksShip and connect your store in order to activate these advanced features:</p>
									</div>
									<div class="trackship-upsell-content">
										<ul>
											<li>Automatically track your shipments with 100+ shipping providers.</li>
											<li>Display Shipment Status and latest shipment status, update date and est. delivery date on WooCommerce orders admin.</li>
											<li>Option to manually get shipment tracking updates for orders.</li>
											<li>Automatically change order status to Delivered once the shipment is delivered to your customers.</li>
											<li>Option to filter orders with invalid tracking numbers or by shipment status event in orders admin</li>
											<li>Send personalized emails to notify the customer when their shipments are In Transit, Out For Delivery, Delivered or have an exception.</li>
											<li>Direct customers to a Tracking page on your store.</li>
										</ul>
										<div class="text-center"><a href="https://trackship.info/?utm_source=wpadmin&utm_campaign=tspage" target="_blank" class="button-primary btn_green2 btn_large">SIGNUP NOW</a></div>
									</div>
								</div>
							</div>
							<?php }	?>
							<div class="submit">								
								<a href="<?php echo admin_url( 'admin.php?page=woocommerce-advanced-shipment-tracking' ); ?>" class="button-primary btn_ast2 btn_large"><?php _e( 'Save', 'woo-advanced-shipment-tracking' ); ?></a>							
							</div>	
						</div>
					</div>
			</div>
			<!---->
		</div>
	<?php }

	/**
	 * Check if we should do any redirect.
	 */
	public function maybe_redirect() {

		// Bail if no activation redirect.
		if ( ! get_transient( '_ast_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient.
		delete_transient( '_ast_activation_redirect' );		

		// Bail if activating from network, or bulk.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) { // WPCS: CSRF ok, input var ok.
			return;
		}		
		
		$redirect = admin_url( 'index.php?page=ast-getting-started' );
		wp_safe_redirect( $redirect );
		exit;
	}	
	
	/**
	 * Scripts for loading the welcome screen Vue instance.
	 */
	public function welcome_scripts() {

		if(!isset($_GET['page'])) {
			return;
		}
		
		if( $_GET['page'] != 'ast-getting-started') {
			return;
		}
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		wp_enqueue_style( 'font-awesome',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/font-awesome.min.css', array(), '4.7' );

		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
		wp_enqueue_script( 'select2');
		
		wp_enqueue_style( 'shipment_tracking_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );
		
		wp_enqueue_script( 'woocommerce-advanced-shipment-tracking-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/admin.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version);
		
		wp_localize_script( 'woocommerce-advanced-shipment-tracking-js', 'ast_admin_js', array(
			'i18n' => array(
				'get_shipment_status_message' => __( 'Get Shipment Status is limited to 100 orders at a time, please select up to 100 orders.', 'woo-advanced-shipment-tracking' ),
			),			
		) );
		
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.4' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		
		wp_enqueue_script( 'selectWoo');
		wp_enqueue_script( 'wc-enhanced-select');
		
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles' );
		
		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		wp_enqueue_script( 'jquery-tiptip' );
		wp_enqueue_script( 'jquery-blockui' );
		wp_enqueue_script( 'wp-color-picker' );		
		wp_enqueue_script( 'jquery-ui-sortable' );		
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');		
		wp_enqueue_style('thickbox');		
		
		wp_enqueue_style( 'material-css',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/material.css', array(), wc_advanced_shipment_tracking()->version );		
		wp_enqueue_script( 'material-js', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/material.min.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );						
		
		wp_enqueue_script( 'ajax-queue', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/jquery.ajax.queue.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version);
				
		wp_enqueue_script( 'advanced_shipment_tracking_settings', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/settings.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );		
		
		wp_enqueue_script( 'shipment_tracking_table_rows', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/shipping_row.js' , array( 'jquery', 'wp-util' ), wc_advanced_shipment_tracking()->version );
		wp_localize_script( 'shipment_tracking_table_rows', 'shipment_tracking_table_rows', array(
			'i18n' => array(				
				'data_saved'	=> __( 'Data saved successfully.', 'woo-advanced-shipment-tracking' ),
				'delete_provider' => __( 'Really delete this entry? This will not be undo.', 'woo-advanced-shipment-tracking' ),
				'upload_only_csv_file' => __( 'You can upload only csv file.', 'woo-advanced-shipment-tracking' ),
				'browser_not_html' => __( 'This browser does not support HTML5.', 'woo-advanced-shipment-tracking' ),
				'upload_valid_csv_file' => __( 'Please upload a valid CSV file.', 'woo-advanced-shipment-tracking' ),
			),
			'delete_rates_nonce' => wp_create_nonce( "delete-rate" ),
		) );
		wp_enqueue_media();
		
		wp_enqueue_style( 'shipment_tracking_welcome_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/welcome.css', array(), wc_advanced_shipment_tracking()->version );
		wp_enqueue_script( 'shipment_tracking_welcome_script', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/welcome.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );	
	}
	
	public function remove_all_admin_notice(){
		if( isset($_GET['page']) && $_GET['page'] == 'ast-getting-started'){	
			remove_all_actions('admin_notices');
			remove_all_actions('all_admin_notices'); 
		}
	}
}

new AST_Welcome();
