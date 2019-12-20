<?php
/**
 * html code for trackship tab
 */

?>
<section id="content3" class="tab_section">
	<div class="d_table" style="">
		<div class="tab_inner_container">
			<form method="post" id="wc_ast_trackship_form" action="" enctype="multipart/form-data">    		
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

				<table class="form-table heading-table">
					<tbody>
						<tr valign="top">
							<td>
								<h3 style=""><?php _e( 'General Settings', 'woo-advanced-shipment-tracking' ); ?></h3>
							</td>					
						</tr>
					</tbody>
				</table>		
				<?php $this->get_html( $this->get_trackship_general_data() ); ?>
				<table class="form-table">
					<tbody>
						<tr valign="top">						
							<td class="button-column">
								<div class="submit">								
									<button name="save" class="button-primary woocommerce-save-button btn_ast2 btn_large" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>
									<div class="spinner"></div>								
									<?php wp_nonce_field( 'wc_ast_trackship_form', 'wc_ast_trackship_form' );?>
									<input type="hidden" name="action" value="wc_ast_trackship_form_update">
								</div>	
							</td>
						</tr>
					</tbody>
				</table>
						
				<table class="form-table heading-table">
					<tbody>
						<tr valign="top">
							<td>
								<h3 style=""><?php _e( 'Tracking Page', 'woo-advanced-shipment-tracking' ); ?></h3>
							</td>					
						</tr>
					</tbody>
				</table>
				<?php $this->get_html( $this->get_trackship_page_data() );  ?>
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<td>
								<a href="<?php echo get_home_url(); ?>?action=preview_tracking_page" class="tracking-preview-link" target="_blank" style="line-height: 30px;"><?php _e('Click to preview the tracking page', 'woo-advanced-shipment-tracking'); ?></a>
								<p class="tracking-preview-desc"><?php _e('PLEASE NOTE - make sure to save your settings before preview.', 'woo-advanced-shipment-tracking'); ?></p>
							</td>
						</tr>
						<tr valign="top">						
							<td class="button-column">
								<div class="submit">								
									<button name="save" class="button-primary woocommerce-save-button btn_ast2 btn_large" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>
									<div class="spinner"></div>								
									<?php wp_nonce_field( 'wc_ast_trackship_form', 'wc_ast_trackship_form' );?>
									<input type="hidden" name="action" value="wc_ast_trackship_form_update">
								</div>	
							</td>
						</tr>
					</tbody>
				</table>
				<?php
				if($wc_ast_api_key){
				?>		        
				<?php } ?>		
				<h3 class="table-heading"><?php _e('Shipment Status Notifications ', 'woo-advanced-shipment-tracking'); ?></h3>	
				<?php 
					$wcast_enable_delivered_email = get_option('woocommerce_customer_delivered_order_settings'); 
					$wcast_enable_pretransit_email = get_theme_mod('wcast_enable_pretransit_email');
					$wcast_enable_intransit_email = get_theme_mod('wcast_enable_intransit_email');
					$wcast_enable_outfordelivery_email = get_theme_mod('wcast_enable_outfordelivery_email');
					$wcast_enable_failure_email = get_theme_mod('wcast_enable_failure_email');
					$wcast_enable_delivered_status_email = get_theme_mod('wcast_enable_delivered_status_email');
					$wcast_enable_returntosender_email = get_theme_mod('wcast_enable_returntosender_email');
					$wcast_enable_availableforpickup_email = get_theme_mod('wcast_enable_availableforpickup_email');	
					$wc_ast_api_key = get_option('wc_ast_api_key');			
					//echo '<pre>';print_r($wcast_enable_delivered_email['enabled']);echo '</pre>';		
				?>						
				<section class="ac-container">		
				
				<div class="headig_label <?php if($wcast_enable_intransit_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">	
					<img class="email-icon" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/css/icons/In-Transit-512.png">
					<span class="email_status_span">
						<span class="mdl-list__item-secondary-action shipment_status_toggle">
							<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="wcast_enable_intransit_email">
								<input type="checkbox" name="wcast_enable_intransit_email" id="wcast_enable_intransit_email" class="mdl-switch__input" value="yes" <?php if($wcast_enable_intransit_email == 1) { echo 'checked'; } ?> />
							</label>
						</span>			
					</span>
					<a href="<?php echo wcast_intransit_customizer_email::get_customizer_url('customer_intransit_email','shipment-status-notifications') ?>" class="email_heading"><?php _e('In Transit', 'woo-advanced-shipment-tracking'); ?></a>
					<a class="edit_customizer_a" href="<?php echo wcast_intransit_customizer_email::get_customizer_url('customer_intransit_email','shipment-status-notifications') ?>"><?php _e('Edit', 'woocommerce'); ?></a>
					<p class="shipment_about"><?php _e('Carrier has accepted or picked up shipment from shipper. The shipment is on the way.', 'woo-advanced-shipment-tracking'); ?></p>
				</div>			
		
				<div class="headig_label <?php if($wcast_enable_returntosender_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
					<img class="email-icon" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/css/icons/return-to-sender-512.png">		
					<span class="email_status_span">
						<span class="mdl-list__item-secondary-action shipment_status_toggle">
							<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="wcast_enable_returntosender_email">
								<input type="checkbox" name="wcast_enable_returntosender_email" id="wcast_enable_returntosender_email" class="mdl-switch__input" value="yes" <?php if($wcast_enable_returntosender_email == 1) { echo 'checked'; } ?> />
							</label>
						</span>
					</span>
					<a href="<?php echo wcast_returntosender_customizer_email::get_customizer_url('customer_returntosender_email','shipment-status-notifications') ?>" class="email_heading"><?php _e('Return To Sender', 'woo-advanced-shipment-tracking'); ?></a>
					<a class="edit_customizer_a" href="<?php echo wcast_returntosender_customizer_email::get_customizer_url('customer_returntosender_email','shipment-status-notifications') ?>"><?php _e('Edit', 'woocommerce'); ?></a>
					<p class="shipment_about"><?php _e('Shipment is returned to sender', 'woo-advanced-shipment-tracking'); ?></p>
				</div>
		
				<div class="headig_label <?php if($wcast_enable_availableforpickup_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">	
					<img class="email-icon" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/css/icons/available-for-picup-512.png">		
					<span class="email_status_span">
						<span class="mdl-list__item-secondary-action shipment_status_toggle">
							<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="wcast_enable_availableforpickup_email">
								<input type="checkbox" name="wcast_enable_availableforpickup_email" id="wcast_enable_availableforpickup_email" class="mdl-switch__input" value="yes" <?php if($wcast_enable_availableforpickup_email == 1) { echo 'checked'; } ?> />
							</label>
						</span>
					</span>
					<a href="<?php echo wcast_availableforpickup_customizer_email::get_customizer_url('customer_availableforpickup_email','shipment-status-notifications') ?>" class="email_heading"><?php _e('Available For Pickup', 'woo-advanced-shipment-tracking'); ?></a>
					<a class="edit_customizer_a" href="<?php echo wcast_availableforpickup_customizer_email::get_customizer_url('customer_availableforpickup_email','shipment-status-notifications') ?>"><?php _e('Edit', 'woocommerce'); ?></a>
					<p class="shipment_about"><?php _e('The shipment is ready to pickup.', 'woo-advanced-shipment-tracking'); ?></p>
				</div>
				<div class="headig_label <?php if($wcast_enable_outfordelivery_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
					<img class="email-icon" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/css/icons/Out-for-Delivery-512.png">
					<span class="email_status_span">
						<span class="mdl-list__item-secondary-action shipment_status_toggle">
							<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="wcast_enable_outfordelivery_email">
								<input type="checkbox" name="wcast_enable_outfordelivery_email" id="wcast_enable_outfordelivery_email" class="mdl-switch__input" value="yes" <?php if($wcast_enable_outfordelivery_email == 1) { echo 'checked'; } ?> />
							</label>
						</span>				
					</span>
					<a href="<?php echo wcast_outfordelivery_customizer_email::get_customizer_url('customer_outfordelivery_email','shipment-status-notifications') ?>" class="email_heading"><?php _e('Out For Delivery', 'woo-advanced-shipment-tracking'); ?></a>
					<a class="edit_customizer_a" href="<?php echo wcast_outfordelivery_customizer_email::get_customizer_url('customer_outfordelivery_email','shipment-status-notifications') ?>"><?php _e('Edit', 'woocommerce'); ?></a>
					<p class="shipment_about"><?php _e('Carrier is about to deliver the shipment', 'woo-advanced-shipment-tracking'); ?></p>
				</div>	
		
				<div class="delivered_shipment_label headig_label <?php if($wcast_enable_delivered_status_email == 1){ echo 'enable'; } else{ echo 'disable'; }?> <?php if($wcast_enable_delivered_email['enabled'] === 'yes' && get_option('wc_ast_status_delivered') == 1){ echo 'delivered_enabel'; } ?>">
					<img class="email-icon" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/css/icons/Delivered-512.png">
					<span class="email_status_span">
						<span class="mdl-list__item-secondary-action shipment_status_toggle">
							<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="wcast_enable_delivered_status_email">
								<input type="checkbox" name="wcast_enable_delivered_status_email" id="wcast_enable_delivered_status_email" class="mdl-switch__input" value="yes" <?php if($wcast_enable_delivered_status_email == 1 && $wcast_enable_delivered_email['enabled'] != 'yes') { echo 'checked'; } ?> <?php if($wcast_enable_delivered_email['enabled'] === 'yes' && get_option('wc_ast_status_delivered') == 1){ echo 'disabled'; }?> />
							</label>
						</span>				
					</span>			
					<a href="<?php echo wcast_delivered_customizer_email::get_customizer_url('customer_delivered_status_email','shipment-status-notifications') ?>" class="email_heading <?php if($wcast_enable_delivered_email['enabled'] === 'yes' && get_option('wc_ast_status_delivered') == 1){ echo 'disabled_link'; }?>"><?php _e('Delivered', 'woo-advanced-shipment-tracking'); ?></a>
					<a class="edit_customizer_a <?php if($wcast_enable_delivered_email['enabled'] === 'yes' && get_option('wc_ast_status_delivered') == 1){ echo 'disabled_link'; }?>" href="<?php echo wcast_delivered_customizer_email::get_customizer_url('customer_delivered_status_email','shipment-status-notifications') ?>"><?php _e('Edit', 'woocommerce'); ?></a>
					<p class="shipment_about"><?php _e('The shipment was delivered successfully', 'woo-advanced-shipment-tracking'); ?></p>
					<p class="delivered_message <?php if($wcast_enable_delivered_email['enabled'] === 'yes' && get_option('wc_ast_status_delivered') == 1){ echo 'disable_delivered'; }?>"><?php _e("You already have delivered email enabled, to enable this email you'll need to disable the order status delivered in settings.", 'woo-advanced-shipment-tracking'); ?></p>
				</div>	
					
				<div class="headig_label <?php if($wcast_enable_failure_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
					<img class="email-icon" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/css/icons/failure-512.png">
					<span class="email_status_span">
						<span class="mdl-list__item-secondary-action shipment_status_toggle">
							<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="wcast_enable_failure_email">
								<input type="checkbox" name="wcast_enable_failure_email" id="wcast_enable_failure_email" class="mdl-switch__input" value="yes" <?php if($wcast_enable_failure_email == 1) { echo 'checked'; } ?> />
							</label>
						</span>				
					</span>
					<a href="<?php echo wcast_failure_customizer_email::get_customizer_url('customer_failure_email','shipment-status-notifications') ?>" class="email_heading"><?php _e('Failed Attempt', 'woo-advanced-shipment-tracking'); ?></a>
					<a class="edit_customizer_a" href="<?php echo wcast_failure_customizer_email::get_customizer_url('customer_failure_email','shipment-status-notifications') ?>"><?php _e('Edit', 'woocommerce'); ?></a>
					<p class="shipment_about"><?php _e('Carrier attempted to deliver but failed, and usually leaves a notice and will try to deliver the package again.', 'woo-advanced-shipment-tracking'); ?></p>
				</div>																		
				</section>										
				
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
				<?php }
			?>
			</form>
		</div>
	<?php 
	if($wc_ast_api_key){
		include 'zorem_admin_sidebar.php';
	}
	?>
	</div>
</section>