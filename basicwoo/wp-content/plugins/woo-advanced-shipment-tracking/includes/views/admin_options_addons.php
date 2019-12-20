<?php
/**
 * html code for tools tab
 */
?>
<section id="content6" class="tab_section">
	<div class="d_table" style="">
		<div class="tab_inner_container">	
			<form method="post" id="wc_ast_addons_form" class="addons_inner_container" action="" enctype="multipart/form-data"> 
				<div class="ast_addons_section">
					<table class="form-table heading-table">
						<tbody>
							<tr valign="top" class="addons_header ts_addons_header">
								<td>
									<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ts-banner.jpg">
								</td>
							</tr>
							<tr valign="top">
								<td>
									<h3 style=""><?php _e( 'TrackShip', 'woo-advanced-shipment-tracking' ); ?></h3>
								</td>					
							</tr>
						</tbody>
					</table>
					<table class="form-table">
						<tbody>						
							<tr style="height: 140px;">
								<td>
									<?php 
									$wc_ast_api_key = get_option('wc_ast_api_key');
									if($wc_ast_api_key){
										echo '<p>';
										_e( 'You are now connected with TrackShip! TrackShip makes it effortless to automate your post shipping operations and get tracking and delivery status updates directly in the WooCommerce admin.', 'woo-advanced-shipment-tracking' ); 
										echo '</p>';
									} else{ ?>
										<p style="margin-top: 4px;">TracksShip is a premium shipment tracking API flatform that fully integrates with WooCommerce with the Advanced Shipment Tracking. TrackShip automates the order management workflows, reduces customer inquiries, reduces time spent on customer service, and improves the post-purchase experience and satisfaction of your customers.</p>
										<p style="margin-top: 4px;">You must have account TracksShip and connect your store in order to activate these advanced features:</p>										
									<?php } ?>													
								</td>																
							</tr>
							<tr>
								<td class="forminp">
									<?php if($wc_ast_api_key){ ?>
									<fieldset>
										<a href="https://my.trackship.info/" target="_blank" class="button-primary btn_ast2 btn_large">
											<span class=""><label><?php _e( 'Connected', 'woo-advanced-shipment-tracking' ); ?></label><span class="dashicons dashicons-yes"></span></span>
										</a>
									</fieldset>					
									<?php } else{ ?>
									<fieldset>
										<a href="https://trackship.info/?utm_source=wpadmin&utm_campaign=tspage" target="_blank" class="button-primary btn_ast2 btn_large">SIGNUP NOW</a>
									</fieldset>		
									<?php } ?>		
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="ast_addons_section">	
					<table class="form-table heading-table">
						<tbody>
							<tr valign="top" class="addons_header">
								<td>
									<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/Tracking-Per-Item-addon.jpg">
								</td>
							</tr>
							<tr valign="top">
								<td>
									<h3 style="">Tracking Per Item Add-on</h3>
								</td>					
							</tr>
						</tbody>
					</table>
					<?php if ( !is_plugin_active( 'ast-tracking-per-order-items/ast-tracking-per-order-items.php' ) ) {
					?>	
					<table class="form-table">
						<tbody>						
							<tr style="height: 140px;">
								<td>
									<p style="margin-top: 4px;">The Tracking Per Item add-on extends the AST plugin and allows to attache order items to tracking number when adding tracking numbers to orders. The attached items will display next to the tracking number on the order emails and my-account</p>
								</td>
							</tr>
						</tbody>
					</table>	
					<table class="form-table">
						<tbody>
							<tr valign="top">						
								<td class="button-column">
									<div class="submit">																
										<a href="https://www.zorem.com/shop/tracking-per-item-ast-add-on/" target="blank" class="button-primary btn_ast2 btn_large"><?php _e( 'Upgrade Now', 'woo-advanced-shipment-tracking' ); ?></a>	
									</div>	
								</td>
							</tr>
						</tbody>
					</table>							
					<?php } else{ ?>
						<div class="license_div">
						<?php
						$this->get_html( $this->get_ast_product_license_data() );				
						?>
						</div>
						<table class="form-table">
							<tbody>
								<tr valign="top">						
									<td class="button-column">
										<div class="submit">	
											<?php												
												if($this->licence_valid() == 'true'){ ?>
													<span class="button-primary btn_ast2 btn_large"><?php _e('Active','woo-advanced-shipment-tracking');?> <span class="dashicons dashicons-yes"></span></span>
													<button name="save" class="button-primary woocommerce-save-button btn_ast2 btn_large" type="submit" value="Deactivate"><?php _e('Deactivate','woo-advanced-shipment-tracking');?></button>
												<?php } else{ ?>
													<button name="save" class="button-primary woocommerce-save-button btn_ast2 btn_large" type="submit" value="Save changes"><?php _e('Activate','woo-advanced-shipment-tracking');?></button>
												<?php } 
											?>											
											<p class="pesan"></p>
											<div class="spinner"></div>								
											<?php wp_nonce_field( 'wc_ast_addons_form', 'wc_ast_addons_form' );?>
											<input type="hidden" id="ast-license-action" name="action" value="<?=$this->licence_valid()  == 'true' ? 'ast_product_license_deactivate':'ast_product_license_activate';?>" />
										</div>										
									</td>
								</tr>
							</tbody>
						</table>
						<?php
					} ?>
				</div>
			</form>
		</div>
	<?php 
		include 'zorem_admin_sidebar.php';
	?>
	</div>
</section>