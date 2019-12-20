<?php
/**
 * html code for bulk upload tab
 */
?>
<section id="content4" class="tab_section">
	<div class="tab_inner_container">	
		<form method="post" id="wc_ast_upload_csv_form" action="" enctype="multipart/form-data">
			<section id="" class="tpage_section" style="display:block;">
			<h3 class="table-heading"><?php _e('Upload CSV', 'woo-advanced-shipment-tracking'); ?></h3>	
			<table class="form-table upload_csv_table">
				<tbody>
					<tr valign="top" class="">
						<td scope="row" class="input_file_cl" colspan="2">
							<input type="file" name="trcking_csv_file" id="trcking_csv_file">
						</td>
					</tr> 
					<tr valign="top" class="">
						<th scope="row" class="th_80">
							<label for=""><?php _e('Replace tracking info if exists? (if not checked, the tracking info will be added)', 'woo-advanced-shipment-tracking'); ?></label>													
						</th>
						<td scope="row" class="th_20">
							<input type="checkbox" id="replace_tracking_info" name="replace_tracking_info" class="" value="1"/>
						</td>
					</tr>
					<tr valign="top" class="">
						<th scope="row" colspan="2">
							<div class="submit">
								<button name="save" class="button-primary btn_ast2 btn_large" type="submit" value="Save"><?php _e('Upload', 'woo-advanced-shipment-tracking'); ?></button>
								<div class="spinner" style="float:none"></div>
								<div class="success_msg" style="display:none;"><?php _e('Settings Saved.', 'woo-advanced-shipment-tracking'); ?></div>
								<div class="error_msg" style="display:none;"></div>
								<?php wp_nonce_field( 'wc_ast_upload_csv_form', 'wc_ast_upload_csv_form' );?>
								<input type="hidden" name="action" value="wc_ast_upload_csv_form_update">
							</div>	
						</th>
					</tr>
					<tr class="bulk_upload_status_tr" style="display:none;">
						<td scope="row" colspan="2">
							<div id="p1" class="mdl-progress mdl-js-progress" style="display:none;"></div>
							<h3 class="progress_title" style="display:none;"><?php _e('Upload Progress - ', 'woo-advanced-shipment-tracking'); ?>
								<span class="progress_number"></span>
							</h3>
							<ol class="csv_upload_status">								
							</ol>
						</td>
					</tr>	
				</tbody>				
			</table>																											
			<h3 class="table-heading"><?php _e('Sample CSV', 'woo-advanced-shipment-tracking'); ?></h3>
			<table class="form-table">
				<tbody>
					<tr valign="top" style="border-bottom: 0;">
						<td>
							<p><?php _e('You can download an example of the csv file:', 'woo-advanced-shipment-tracking'); ?></p>
						</td>
						<td style="text-align:right;">
							<a class="button-primary btn_ast2 btn_large" href="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>/assets/tracking.csv"><?php _e('Download sample csv file', 'woo-advanced-shipment-tracking'); ?></a>
						</td>
					</tr>
					<tr valign="top">
						<td style="padding-top: 0;" colspan="2">
							<p><?php _e('For detailed instructions on how to upload tracking info in bulk, see our', 'woo-advanced-shipment-tracking'); ?> <a class="" href="https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/bulk-import-shipment-tracking/" target="blank"><?php _e('Documentation', 'woo-advanced-shipment-tracking'); ?></a>.</p>	
						</td>
					</tr>
				</tbody>
			</table>					
			</section>	
		</form>	
	</div>	
<?php include 'zorem_admin_sidebar.php';?>	
</section>