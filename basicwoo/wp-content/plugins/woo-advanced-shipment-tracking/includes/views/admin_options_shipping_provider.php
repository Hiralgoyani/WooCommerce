<?php
/**
 * html code for shipping providers tab
 */
?>
<?php $wc_ast_api_key = get_option('wc_ast_api_key'); ?>
<section id="content1" class="tab_section">
	<div class="d_table" style="">
	<div class="tab_inner_container">
		<div class="provider_top">	
			<div class="status_filter">
				<a href="javaScript:void(0);" data-status="active" class="active"><?php _e( 'Active', 'woo-advanced-shipment-tracking'); ?></a>
				<a href="javaScript:void(0);" data-status="inactive"><?php _e( 'Inactive', 'woo-advanced-shipment-tracking'); ?></a>
				<a href="javaScript:void(0);" data-status="custom"><?php _e( 'Custom', 'woo-advanced-shipment-tracking'); ?></a>
				<a href="javaScript:void(0);" data-status="all"><?php _e( 'All', 'woocommerce'); ?></a>
			</div>
			<div class="provider_settings dropdown">
				<span class="dashicons dashicons-admin-generic dropdown_menu"></span>			
			</div>
			<ul class="dropdown-content">
				<li><a href="javaScript:void(0);" class="add_custom_provider" id="add-custom"><?php _e( 'Add Custom Provider', 'woo-advanced-shipment-tracking'); ?></a></li>				
				<li><a href="javaScript:void(0);" class="sync_providers"><?php _e( 'Sync Shipping Providers', 'woo-advanced-shipment-tracking'); ?></a></a></li>				
				<li><?php _e( 'Reset', 'woo-advanced-shipment-tracking'); ?> <a href="javaScript:void(0);" class="reset_active"><?php _e( 'Active', 'woo-advanced-shipment-tracking'); ?></a> | <a href="javaScript:void(0);" class="reset_inactive"><?php _e( 'Inactive', 'woo-advanced-shipment-tracking'); ?></a></li>
			</ul>
			<div class="search_section">
				<span class="dashicons dashicons-search search-icon"></span>
				<input class="provider_search_bar " type="text" name="search_provider" id="search_provider" placeholder="<?php _e( 'Search by provider / country', 'woo-advanced-shipment-tracking'); ?>">		
			</div>		
		</div>
		<div class="provider_list">
			<?php 
				if($default_shippment_providers){
				foreach($default_shippment_providers as $d_s_p){ ?>
				<div class="provider <?php if($d_s_p->display_in_order == 1) { echo 'active_provider'; } ?>">
					<div class="provider_inner">
						<div class="row-1">
							<div class="left-div">
								<a href="<?php echo str_replace("%number%","",$d_s_p->provider_url ); ?>" title="<?php echo str_replace("%number%","",$d_s_p->provider_url ); ?>" target="_blank">
								<?php  if( $d_s_p->shipping_default == 1 ){ ?>
								<img class="provider-thumb" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/shipment-provider-img/<?php echo sanitize_title($d_s_p->provider_name);?>.png?v=<?php echo wc_advanced_shipment_tracking()->version;?>">
								<?php } else{ 
								$custom_thumb_id = $d_s_p->custom_thumb_id;
								$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array('60','60') );
								//echo '<pre>';print_r($custom_thumb_id);echo '</pre>';exit;
								if($custom_thumb_id != 0){ ?>
									<img class="provider-thumb" src="<?php echo $image_attributes[0]; ?>">
								<?php } else{
								?>
									<img class="provider-thumb" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/shipment-provider-img/icon-default.png">
								<?php } ?>
								<?php } ?>					
								</a>
							</div>
							<div class="right-div">
								<a href="<?php echo str_replace("%number%","",$d_s_p->provider_url ); ?>" title="<?php echo str_replace("%number%","",$d_s_p->provider_url ); ?>" target="_blank">
									<span class="provider_name"><?php echo $d_s_p->provider_name; ?></span>
								</a>
								<br>
								<span class="provider_country"><?php
											$search  = array('(US)', '(UK)');
											$replace = array('', '');
											if($d_s_p->shipping_country && $d_s_p->shipping_country != 'Global'){
												echo str_replace($search, $replace, $WC_Countries->countries[$d_s_p->shipping_country]);
											} elseif($d_s_p->shipping_country && $d_s_p->shipping_country == 'Global'){
												echo 'Global';
											}									
											?></span>
							</div>
						</div>
						<div class="row-2">
							<div class="default-provider">
								<?php $default_provider = get_option("wc_ast_default_provider" );?>
								<label for="make_default_<?php echo $d_s_p->id; ?>" id="default_label_<?php echo $d_s_p->id; ?>" class="<?php if($d_s_p->display_in_order != 1) { echo 'disable_label'; } ?>">
									<input type="checkbox" id="make_default_<?php echo $d_s_p->id; ?>" name="make_provider_default" data-id="<?php echo $d_s_p->id; ?>" class="make_provider_default" value="<?php echo sanitize_title( $d_s_p->provider_name )?>" <?php if( $default_provider == sanitize_title( $d_s_p->provider_name ) )echo 'checked';?> <?php if($d_s_p->display_in_order != 1) { echo 'disabled'; } ?>>
									<span>default</span>
								</label>
							</div>
							<div class="provider-status">
								<?php if( $d_s_p->shipping_default == 0 ){ ?>
									<span class="dashicons dashicons-edit edit_provider" data-pid="<?php echo $d_s_p->id; ?>"></span>
									<span class="dashicons dashicons-trash remove" data-pid="<?php echo $d_s_p->id; ?>"></span>													
								<?php } ?>
								<span class="mdl-list__item-secondary-action">
									<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="list-switch-<?php echo $d_s_p->id; ?>">
										<input type="checkbox" name="select_custom_provider[]" id="list-switch-<?php echo $d_s_p->id; ?>" class="mdl-switch__input status_slide" value="<?php echo $d_s_p->id; ?>" <?php if($d_s_p->display_in_order == 1) { echo 'checked'; } ?> />
									</label>
								</span>
							</div>
						</div>	
					</div>	
				</div>	
				<?php } } else{ 
					$status = 'active';
				?>
					<h3><?php echo sprintf(__("You don't have any %s shipping providers.", 'woo-advanced-shipment-tracking'), $status); ?></h3>
				<?php } ?>		
		</div>
		
		<div id="" class="popupwrapper add_provider_popup" style="display:none;">
			<div class="popuprow">
				<h3 class="popup_title"><?php _e( 'Add Custom Shipping Provider', 'woo-advanced-shipment-tracking'); ?></h2>
				<form id="add_provider_form" method="POST" class="add_provider_form">
					<div>
						<input type="text" name="shipping_provider" id="shipping_provider" placeholder="Shipping Provider">
					</div>
					<div>
						<select class="select wcast_shipping_country" name="shipping_country" id="shipping_country">
							<option value=""><?php _e( 'Shipping Country', 'woo-advanced-shipment-tracking' ); ?></option>
							<option value="Global"><?php _e( 'Global', 'woo-advanced-shipment-tracking' ); ?></option>
							<?php 
								foreach($countries as $key=>$val){ ?>
									<option value="<?php echo $key; ?>" ><?php _e( $val, 'woo-advanced-shipment-tracking'); ?></option>
								<?php } ?>
						</select>
					</div>
					<div>
						<input type='text' placeholder='Image' name='thumb_url' class='image_path' value='' id='thumb_url'>
						<input type='hidden' name='thumb_id' class='image_id' placeholder="Image" value='' id='thumb_id' style="">
						<input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload' , 'woo-advanced-shipment-tracking'); ?>" />
					</div>
					<div>
						<input type="text" name="tracking_url" id="tracking_url" placeholder="Tracking URL">
					</div>
					<div class="custom_provider_instruction">
						<a href="https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/setting-shipping-providers/#adding-custom-shipping-provider" target="blank"><?php _e( 'How to add Tracking URL' , 'woo-advanced-shipment-tracking'); ?></a>
					</div>
					<p>		
						<input type="hidden" name="action" value="add_custom_shipment_provider">
						<input type="submit" name="Submit" value="Submit" class="button-primary btn_ast2 btn_large">        
					</p>			
				</form>
			</div>
			<div class="popupclose"></div>
		</div>
		
		<div id="" class="popupwrapper edit_provider_popup" style="display:none;">
			<div class="popuprow">
				<h3 class="popup_title"><?php _e( 'Edit Custom Shipping Provider', 'woo-advanced-shipment-tracking'); ?></h2>
				<form id="edit_provider_form" method="POST" class="edit_provider_form">
					<div>
						<input type="text" name="shipping_provider" id="shipping_provider" value="" placeholder="Shipping Provider">
					</div>
					<div>
						<select class="select wcast_shipping_country" name="shipping_country" id="shipping_country">
							<option value=""><?php _e( 'Shipping Country', 'woo-advanced-shipment-tracking' ); ?></option>
							<option value="Global"><?php _e( 'Global', 'woo-advanced-shipment-tracking' ); ?></option>
							<?php 
								foreach($countries as $key=>$val){ ?>
									<option value="<?php echo $key; ?>" ><?php _e( $val, 'woo-advanced-shipment-tracking'); ?></option>
								<?php } ?>
						</select>
					</div>
					<div>
						<input type='text' placeholder='Image' name='thumb_url' class='image_path' value='' id='thumb_url'>
						<input type='hidden' name='thumb_id' class='image_id' placeholder="Image" value='' id='thumb_id' style="">
						<input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload' , 'woo-advanced-shipment-tracking'); ?>" />
					</div>
					<div>
						<input type="text" name="tracking_url" id="tracking_url" placeholder="Tracking URL">
					</div>
					<div class="custom_provider_instruction">
						<a href="https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/setting-shipping-providers/#adding-custom-shipping-provider" target="blank"><?php _e( 'How to add Tracking URL' , 'woo-advanced-shipment-tracking'); ?></a>
					</div>
					<p>		
						<input type="hidden" name="action" value="update_custom_shipment_provider">
						<input type="hidden" name="provider_id" id="provider_id" value="">
						<input type="submit" name="Submit" value="Update" class="button-primary btn_ast2 btn_large">        
					</p>			
				</form>
			</div>
			<div class="popupclose"></div>
		</div>
		
		<div id="" class="popupwrapper sync_provider_popup" style="display:none;">
			<div class="popuprow">
				<h3 class="popup_title"><?php _e( 'Sync Shipping Providers', 'woo-advanced-shipment-tracking'); ?></h2>
				<p class="sync_message"><?php _e( 'Syncing the shipping providers list add or updates the pre-set shipping providers and will not effect custom shipping providers.', 'woo-advanced-shipment-tracking'); ?></p>
				<ul class="synch_result">
					<li class="providers_added"><?php _e( 'Providers Added', 'woo-advanced-shipment-tracking'); ?> - <span></span></li>
					<li class="providers_updated"><?php _e( 'Providers Updated', 'woo-advanced-shipment-tracking'); ?> - <span></span></li>
					<li class="providers_deleted"><?php _e( 'Providers Deleted', 'woo-advanced-shipment-tracking'); ?> - <span></span></li>
				</ul>			
				<button class="sync_providers_btn button-primary btn_ast2 btn_large"><?php _e( 'Sync Shipping Providers', 'woo-advanced-shipment-tracking'); ?></button>
				<button class="close_synch_popup button-primary btn_ast2 btn_large"><?php _e( 'Close', 'woocommerce'); ?></button>
				<div class="spinner" style=""></div>
			</div>
			<div class="popupclose"></div>
		</div>   	
	</div>
	</div>	
</section>