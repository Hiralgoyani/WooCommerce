<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * View Order: Tracking information
 *
 * Shows tracking numbers view order page
 *
 * @author  WooThemes
 * @package WooCommerce Shipment Tracking/templates/myaccount
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
	
	$display_thumbnail = get_theme_mod('display_shipment_provider_image');
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
	
	$th_column_style = "text-align: center; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;font-size:".$table_header_font_size."px; color: ".$table_header_font_color." ; border: ".$email_border_size."px solid ".$email_border_color."; padding: 12px;";
	
	$td_column_style = "text-align: center; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size:".$table_content_font_size."px;font-weight:".$table_content_font_weight."; color: ".$table_content_font_color." ; border: ".$email_border_size."px solid ".$email_border_color."; padding: 12px;";
	
	$tracking_link_style = "color: ".$tracking_link_font_color." ;background:".$tracking_link_bg_color.";padding: 10px;";
	$hide_table_header = get_theme_mod('hide_table_header');
	$remove_date_from_tracking_info = get_theme_mod('remove_date_from_tracking');
	$show_track_label = get_theme_mod('show_track_label');
	$provider_header_text = get_theme_mod('provider_header_text',$wcast_customizer_settings->defaults['provider_header_text']);
	$tracking_number_header_text = get_theme_mod('tracking_number_header_text',$wcast_customizer_settings->defaults['tracking_number_header_text']);
	$shipped_date_header_text = get_theme_mod('shipped_date_header_text',$wcast_customizer_settings->defaults['shipped_date_header_text']);
	$track_header_text = get_theme_mod('track_header_text',$wcast_customizer_settings->defaults['track_header_text']);
 ?>

	<h2><?php echo apply_filters( 'woocommerce_shipment_tracking_my_orders_title', __( $shipment_tracking_header, 'woo-advanced-shipment-tracking' ) ); ?></h2>
	<p><?php echo $shipment_tracking_header_text; ?></p>
	<style>
	.my_account_tracking td:first-child{
		text-align: left !important;
		padding-left: 10px;
	}
	</style>
	<table class="shop_table shop_table_responsive my_account_tracking" style="width: 100%;border-collapse: collapse;line-height:<?php echo $table_content_line_height; ?>px;background:<?php echo $email_table_backgroud_color; ?>">
		<?php if($hide_table_header != 1){ ?>
		<thead>
			<tr>
				<th class="tracking-provider" style="<?php echo $th_column_style; ?>"><?php _e( $provider_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
				<?php do_action("ast_tracking_my_acoount_header", $order_id, $th_column_style); ?>
				<th class="" style="<?php echo $th_column_style; ?>"><?php _e( $tracking_number_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
				<?php if($remove_date_from_tracking_info != 1){ ?>
				<th class="date-shipped" style="<?php echo $th_column_style; ?>"><?php _e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
				<?php } ?>
				<th class="order-actions" style="<?php echo $th_column_style; ?>"><?php if($show_track_label == 1) { _e( $track_header_text, 'woo-advanced-shipment-tracking' ); }?></th>
			</tr>
		</thead>
		<?php } ?>
		<tbody><?php
		foreach ( $tracking_items as $tracking_item ) {
				?><tr class="tracking">
					<td class="tracking-provider" style="<?php echo $td_column_style; ?>" data-title="<?php _e( 'Provider', 'woo-advanced-shipment-tracking' ); ?>">
						<?php 
						if($display_thumbnail == 1){
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
							?>
							<img style="width: 50px;margin-right: 5px;vertical-align: middle;" src="<?php echo $src; ?>">
						<?php } echo apply_filters( 'ast_provider_title', esc_html( $tracking_item['formatted_tracking_provider'] )); ?>
					</td>
					<?php do_action("ast_tracking_my_account_body", $order_id,$tracking_item, $td_column_style); ?>
					<td class="tracking-number" style="<?php echo $td_column_style; ?>" data-title="<?php _e( 'Tracking Number', 'woo-advanced-shipment-tracking' ); ?>">
						<?php echo esc_html( $tracking_item['tracking_number'] ); ?>
					</td>
					<?php if($remove_date_from_tracking_info != 1){ ?>
					<td class="date-shipped" style="<?php echo $td_column_style; ?>" data-title="<?php _e( 'Date', 'woocommerce' ); ?>" style="text-align:left; white-space:nowrap;">
						<time datetime="<?php echo date( 'Y-m-d', $tracking_item['date_shipped'] ); ?>" title="<?php echo date( 'Y-m-d', $tracking_item['date_shipped'] ); ?>"><?php echo date_i18n( get_option( 'date_format' ), $tracking_item['date_shipped'] ); ?></time>
					</td>
					<?php } ?>
					<td class="order-actions" style="<?php echo $td_column_style; ?>;text-align:center;">
							<?php if ( '' !== $tracking_item['formatted_tracking_link'] ) { 
							$url = str_replace('%number%',$tracking_item['tracking_number'],$tracking_item['formatted_tracking_link']);
							?>
							<a href="<?php echo esc_url( $url ); ?>" target="_blank" class="button" style="<?php echo $tracking_link_style; ?>"><?php _e( 'Track', 'woo-advanced-shipment-tracking' ); ?></a>
							<?php } ?>
					</td>
				</tr><?php
		}
		?></tbody>
	</table>

<?php
endif;