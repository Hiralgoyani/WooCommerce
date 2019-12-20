<?php
/**
 * html code for settings tab
 */
?>
<section id="content2" class="tab_section">
	<div class="tab_inner_container">
		<form method="post" id="wc_ast_settings_form" action="" enctype="multipart/form-data">
			<?php #nonce?>
					
			<table class="form-table heading-table">
				<tbody>
					<tr valign="top">
						<td>
							<h3 style=""><?php _e( 'General Settings', 'woo-advanced-shipment-tracking' ); ?></h3>
						</td>
					</tr>
				</tbody>
			</table>
			<?php $this->get_html( $this->get_settings_data() );?>	
			<table class="form-table">
				<tbody>
					<tr valign="top">						
						<td class="button-column">
							<div class="submit">								
								<button name="save" class="button-primary woocommerce-save-button btn_ast2 btn_large" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>
								<div class="spinner"></div>								
								<?php wp_nonce_field( 'wc_ast_settings_form', 'wc_ast_settings_form' );?>
								<input type="hidden" name="action" value="wc_ast_settings_form_update">
							</div>	
						</td>
					</tr>
				</tbody>
			</table>
						
			<table class="form-table heading-table">
				<tbody>
					<tr valign="top">
						<td>
							<h3 style=""><?php _e( 'Tracking Info Display', 'woo-advanced-shipment-tracking' ); ?></h3>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<td>
							<p style=""><?php _e( 'You can customize the tracking info display on emails and my account', 'woo-advanced-shipment-tracking' ); ?></p>							
						</td>
						<td style="text-align:right;">
							<a href="<?php echo wcast_initialise_customizer_settings::get_customizer_url('default_controls_section','settings') ?>" class="button-primary btn_ast2 btn_large"><?php _e( 'Launch Customizer', 'woo-advanced-shipment-tracking' ); ?> <span class="dashicons dashicons-welcome-view-site"></span></a>
						</td>
					</tr>
				</tbody>
			</table>
			
			<table class="form-table heading-table">
				<tbody>
					<tr valign="top">
						<td>
							<h3 style=""><?php _e( 'Delivered Order Status', 'woo-advanced-shipment-tracking' ); ?></h3>
						</td>
					</tr>
				</tbody>
			</table>
			<?php $this->get_html( $this->get_delivered_data() );?>			
			<p class="description-below-table"><?php echo sprintf(__('<strong>PLEASE NOTE</strong> - If you use the custom order status "Delivered", when you deactivate the plugin, you must register this order status in function.php in order to see these orders in the orders admin. You can find the <a href="%s" target="blank">snippet</a> to use in functions.php here or you can manually change all your "delivered" order to "completed" before deactivating the plugin.', 'woo-advanced-shipment-tracking'), 'https://gist.github.com/zorem/6f09162fe91eab180a76a621ce523441'); ?></p>
			<table class="form-table">
				<tbody>
					<tr valign="top">						
						<td class="button-column">
							<div class="submit">								
								<button name="save" class="button-primary woocommerce-save-button btn_ast2 btn_large" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>
								<div class="spinner"></div>								
								<?php wp_nonce_field( 'wc_ast_settings_form', 'wc_ast_settings_form' );?>
								<input type="hidden" name="action" value="wc_ast_settings_form_update">
							</div>	
						</td>
					</tr>
				</tbody>
			</table>

			<table class="form-table heading-table">
				<tbody>
					<tr valign="top">
						<td>
							<h3 style=""><?php _e( 'Partially Shipped Order Status', 'woo-advanced-shipment-tracking' ); ?></h3>
						</td>
					</tr>
				</tbody>
			</table>
			<?php $this->get_html( $this->get_partial_shipped_data() );?>			
			<p class="description-below-table"><?php echo sprintf(__('<strong>PLEASE NOTE</strong> - If you use the custom order status "Partially Shipped", when you deactivate the plugin, you must register this order status in function.php in order to see these orders in the orders admin. You can find the <a href="%s" target="blank">snippet</a> to use in functions.php here or you can manually change all your "Partially Shipped" order to "completed" before deactivating the plugin.', 'woo-advanced-shipment-tracking'), 'https://gist.github.com/zorem/acc273dccad16095836b8aab058dbe93'); ?></p>
			<table class="form-table">
				<tbody>
					<tr valign="top">						
						<td class="button-column">
							<div class="submit">								
								<button name="save" class="button-primary woocommerce-save-button btn_ast2 btn_large" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>
								<div class="spinner"></div>								
								<?php wp_nonce_field( 'wc_ast_settings_form', 'wc_ast_settings_form' );?>
								<input type="hidden" name="action" value="wc_ast_settings_form_update">
							</div>	
						</td>
					</tr>
				</tbody>
			</table>			
		</form>
	</div>	
	<?php include 'zorem_admin_sidebar.php';?>
</section>