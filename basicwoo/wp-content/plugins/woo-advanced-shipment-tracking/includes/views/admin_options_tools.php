<?php
/**
 * html code for tools tab
 */
?>
<section id="content5" class="tab_section">
	<div class="d_table" style="">
		<div class="tab_inner_container">			
			<table class="form-table heading-table">
				<tbody>
					<tr valign="top">
						<td>
							<h3 style=""><?php _e( 'Get Shipment Status', 'woo-advanced-shipment-tracking' ); ?></h3>
						</td>					
					</tr>
				</tbody>
			</table>
			<table class="form-table">
				<tbody>						
					<tr>
						<td>
							<p><?php _e( 'You can send all your orders from the last 30 days to get shipment status from TrackShip:', 'woo-advanced-shipment-tracking' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>				
			<?php $this->get_html( $this->get_trackship_bulk_actions_data() ); ?>							
		</div>
	<?php 
		if($wc_ast_api_key){ include 'zorem_admin_sidebar.php'; }
	?>
	</div>
</section>