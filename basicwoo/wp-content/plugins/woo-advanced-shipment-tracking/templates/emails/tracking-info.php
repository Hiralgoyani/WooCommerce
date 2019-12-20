<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipment Tracking
 *
 * Shows tracking information in the HTML order email
 *
 * @author  WooThemes
 * @package WooCommerce Shipment Tracking/templates/email
 * @version 1.6.4
 */
if ( $tracking_items ) : 
	$wcast_customizer_settings = new wcast_initialise_customizer_settings();
	$table_header_font_size = '';
	$table_header_font_color = '';
	$table_content_font_size = '';
	$table_content_font_color = '';
	$email_border_color = '';
	$email_border_size = '';
	$shipment_tracking_header_text = '';
	$email_table_backgroud_color = '';
	$tracking_link_font_color = '';
	$tracking_link_bg_color = '';
	$table_content_line_height = '';
	$table_content_font_weight = '';
	$header_content_text_align = '';
	$table_padding = '12';
	$tracking_link_border = 'underline';
	
	$display_thumbnail = get_theme_mod('display_shipment_provider_image');
	
	if(is_rtl()){
		$header_content_text_align = 'right';
	} elseif(get_theme_mod('header_content_text_align')){
		$header_content_text_align = get_theme_mod('header_content_text_align');
	}
	
	if(get_theme_mod('table_padding')){$table_padding = get_theme_mod('table_padding');}
	if(get_theme_mod('table_border_color')){ $email_border_color = get_theme_mod('table_border_color'); } else{ $email_border_color = "#e4e4e4"; }
	if(get_theme_mod('table_border_size')){ $email_border_size = get_theme_mod('table_border_size'); } else{ $email_border_size = "1"; }
	if(get_theme_mod('header_text_change')){ $shipment_tracking_header = get_theme_mod('header_text_change'); } else{ $shipment_tracking_header = "Tracking Information"; }
	
	if(get_theme_mod('additional_header_text')){ $shipment_tracking_header_text = get_theme_mod('additional_header_text'); } 
	
	$email_table_backgroud_color = get_theme_mod('table_bg_color');
	$table_content_line_height = get_theme_mod('table_content_line_height');
	$table_content_font_weight = get_theme_mod('table_content_font_weight');
	
	if(get_theme_mod('table_header_font_size')){ $table_header_font_size = get_theme_mod('table_header_font_size'); }
	if(get_theme_mod('table_header_font_color')){ $table_header_font_color = get_theme_mod('table_header_font_color'); } else{ $table_header_font_color =  "#737373"; }
	
	if(get_theme_mod('table_content_font_size')){ $table_content_font_size = get_theme_mod('table_content_font_size'); }
	
	if(get_theme_mod('table_content_font_color')){ $table_content_font_color = get_theme_mod('table_content_font_color'); } else{ $table_content_font_color =  "#737373"; }
	
	if(get_theme_mod('tracking_link_font_color')){ $tracking_link_font_color = get_theme_mod('tracking_link_font_color'); }
	
	if(get_theme_mod('tracking_link_bg_color')){ $tracking_link_bg_color = get_theme_mod('tracking_link_bg_color'); }
	
	if(get_theme_mod('tracking_link_border')){ 
		$tracking_link_border = 'underline';	
	} else{
		$tracking_link_border = 'unset';	
	}
	
	$th_column_style = "text-align: ".$header_content_text_align."; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;font-size:".$table_header_font_size."px; color: ".$table_header_font_color." ; border: ".$email_border_size."px solid ".$email_border_color."; padding: ".$table_padding."px;";
	
	$td_column_style = "text-align: ".$header_content_text_align."; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size:".$table_content_font_size."px;font-weight:".$table_content_font_weight."; color: ".$table_content_font_color." ; border: ".$email_border_size."px solid ".$email_border_color."; padding: ".$table_padding."px;";
	
	$tracking_link_style = "color: ".$tracking_link_font_color." ;background:".$tracking_link_bg_color.";padding: 10px;text-decoration:".$tracking_link_border."";
	
	$hide_table_header = get_theme_mod('hide_table_header');	
	$remove_date_from_tracking_info = get_theme_mod('remove_date_from_tracking');
	$show_track_label = get_theme_mod('show_track_label');
	
	$provider_header_text = get_theme_mod('provider_header_text',$wcast_customizer_settings->defaults['provider_header_text']);
	$tracking_number_header_text = get_theme_mod('tracking_number_header_text',$wcast_customizer_settings->defaults['tracking_number_header_text']);
	$shipped_date_header_text = get_theme_mod('shipped_date_header_text',$wcast_customizer_settings->defaults['shipped_date_header_text']);
	$track_header_text = get_theme_mod('track_header_text',$wcast_customizer_settings->defaults['track_header_text']);
	
	if(isset( $_REQUEST['wcast-tracking-preview'] ) && '1' === $_REQUEST['wcast-tracking-preview']){
		$preview = true;
	} else{
		$preview = false;
	}
	$text_align = is_rtl() ? 'right' : 'left'; 
	?>
	<h2 class="header_text" style="text-align:<?php echo $text_align; ?>"><?php echo apply_filters( 'woocommerce_shipment_tracking_my_orders_title', __( $shipment_tracking_header, 'woo-advanced-shipment-tracking' ) ); ?></h2>
	<p class="addition_header"><?php echo $shipment_tracking_header_text; ?></p>
	<table class="td tracking_table" cellspacing="0" cellpadding="6" style="width: 100%;border-collapse: collapse;line-height:<?php echo $table_content_line_height; ?>px;background:<?php echo $email_table_backgroud_color; ?>" border="1">
		<?php if($preview){ ?>
		<thead class="<?php if($hide_table_header){ echo 'hide'; }?>">
			<tr>
				<th class="tracking-provider" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php _e( $provider_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
				<?php do_action("ast_tracking_email_header", $order_id, $th_column_style); ?>
				<th class="tracking-number" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php _e( $tracking_number_header_text, 'woo-advanced-shipment-tracking' ); ?></th>												
				<?php if($preview){ ?>
					<th class="date-shipped <?php if($remove_date_from_tracking_info == 1){ echo 'hide'; } ?>" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php _e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
				<?php } else{
						if($remove_date_from_tracking_info != 1){ ?>
							<th class="date-shipped" style="<?php echo $th_column_style; ?>"><span class="nobr"><?php _e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
						<?php }
					} ?>
				<?php if($preview){ ?>
				<th class="order-actions" scope="col" class="td" style="<?php echo $th_column_style; ?>"><span class="track_label <?php if($show_track_label != 1){ echo 'hide'; } ?>"><?php _e( $track_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
				<?php } else{ ?>
					<th class="order-actions" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php if($show_track_label == 1){ _e( $track_header_text, 'woo-advanced-shipment-tracking' ); } ?></th>
				<?php } ?>
			</tr>
		</thead>
		<?php } else{ ?>
		<thead style="<?php if($hide_table_header){ echo 'display:none'; }?>">
			<tr>
				<th class="tracking-provider" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php _e( $provider_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
				<?php do_action("ast_tracking_email_header", $order_id, $th_column_style); ?>
				<th class="tracking-number" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php _e( $tracking_number_header_text, 'woo-advanced-shipment-tracking' ); ?></th>				
				<?php if($preview){ ?>
					<th class="date-shipped <?php if($remove_date_from_tracking_info == 1){ echo 'hide'; } ?>" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php _e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
				<?php } else{
						if($remove_date_from_tracking_info != 1){ ?>
							<th class="date-shipped" style="<?php echo $th_column_style; ?>"><span class="nobr"><?php _e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
						<?php }
					} ?>
				<?php if($preview){ ?>
				<th class="order-actions" scope="col" class="td" style="<?php echo $th_column_style; ?>"><span class="track_label <?php if($show_track_label != 1){ echo 'hide'; } ?>"><?php _e( $track_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
				<?php } else{ ?>
					<th class="order-actions" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php if($show_track_label == 1){ _e( $track_header_text, 'woo-advanced-shipment-tracking' ); } ?></th>
				<?php } ?>
			</tr>
		</thead>	
		<?php } ?>

		<tbody><?php
		foreach ( $tracking_items as $tracking_item ) {			
				?><tr class="tracking">
					<td class="tracking-provider" data-title="<?php _e( 'Provider', 'woo-advanced-shipment-tracking' ); ?>" style="<?php echo $td_column_style; ?>">
						<?php 
						global $wpdb;		
						$woo_shippment_table_name = wc_advanced_shipment_tracking()->table;	
						$shippment_provider = $wpdb->get_results( "SELECT * FROM $woo_shippment_table_name WHERE provider_name='".$tracking_item['formatted_tracking_provider']."'" );
						$custom_thumb_id = $shippment_provider['0']->custom_thumb_id;
						//echo $custom_thumb_id;
						if($custom_thumb_id == 0 && $shippment_provider['0']->shipping_default == 1){
							$src = wc_advanced_shipment_tracking()->plugin_dir_url()."assets/shipment-provider-img/".sanitize_title($tracking_item['formatted_tracking_provider']).".png";
						} else{
							$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array('60','60') );
							if($image_attributes[0]){
								$src = $image_attributes[0];	
							} else{
								$src = wc_advanced_shipment_tracking()->plugin_dir_url()."assets/shipment-provider-img/icon-default.png";	
							}							
						}
						if($preview){ ?>
							<img style="width: 50px;margin-right: 5px;vertical-align: middle;" class="<?php if($display_thumbnail != 1){ echo 'hide'; } ?>" src="<?php echo $src; ?>">
						<?php } else{
						if($display_thumbnail == 1){ ?>
						<img style="width: 50px;margin-right: 5px;vertical-align: middle;" src="<?php echo $src; ?>"><?php } }
						echo apply_filters( 'ast_provider_title', esc_html( $tracking_item['formatted_tracking_provider'] )); ?>
					</td>

					<?php do_action("ast_tracking_email_body", $order_id,$tracking_item, $td_column_style); ?>

					<td class="tracking-number" data-title="<?php _e( 'Tracking Number', 'woo-advanced-shipment-tracking' ); ?>" style="<?php echo $td_column_style; ?>">
						<?php echo esc_html( $tracking_item['tracking_number'] ); ?>
					</td>
					<?php if($preview){ ?>
						<td class="date-shipped <?php if($remove_date_from_tracking_info == 1){ echo 'hide'; } ?>" data-title="<?php _e( 'Status', 'woocommerce' ); ?>" style="<?php echo $td_column_style; ?>">
							<time datetime="<?php echo date( 'Y-m-d', $tracking_item['date_shipped'] ); ?>" title="<?php echo date( 'Y-m-d', $tracking_item['date_shipped'] ); ?>"><?php echo date_i18n( get_option( 'date_format' ), $tracking_item['date_shipped'] ); ?></time>
						</td>						
					<?php } else{ 
						if($remove_date_from_tracking_info != 1){ ?>
							<td class="date-shipped" style="<?php echo $td_column_style; ?>" data-title="<?php _e( 'Date', 'woocommerce' ); ?>" style="text-align:left; white-space:nowrap;">
								<time datetime="<?php echo date( 'Y-m-d', $tracking_item['date_shipped'] ); ?>" title="<?php echo date( 'Y-m-d', $tracking_item['date_shipped'] ); ?>"><?php echo date_i18n( get_option( 'date_format' ), $tracking_item['date_shipped'] ); ?></time>
							</td>
						<?php } 
						} ?>

					<td class="order-actions" style="<?php echo $td_column_style; ?>">
							<?php if($tracking_item['formatted_tracking_link']){ ?>
								<?php $url = str_replace('%number%',$tracking_item['tracking_number'],$tracking_item['formatted_tracking_link']); ?>	
								<a href="<?php echo esc_url( $url ); ?>" style="<?php echo $tracking_link_style; ?>" target="_blank"><?php _e( 'Track', 'woo-advanced-shipment-tracking' ); ?></a>
							<?php } ?>
					</td>
				</tr><?php
		}
		?></tbody>
	</table><br/><br/>

<?php
endif;