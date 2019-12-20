<?php
/**
 * html code for trackip tab sidebar
 */
?>
<div class="zorem_admin_sidebar">
	<div class="ts_launch zorem-sidebar__section">
    	<img src="https://trackship.info/wp-content/uploads/2019/08/trackship-400.png" alt="" style="max-width: 60%;">
		
		<p><a href="https://my.trackship.info" target="_blank" class="button button-primary btn_green" target="_blank"><span><?php _e( 'Dashboard', 'woocommerce' ); ?></span><i class="icon-angle-right"></i></a></p>
		
		<p><a href="https://trackship.info/documentation/" target="_blank" class="button button-primary btn_green" target="_blank"><span><?php _e( 'Documentation', 'woo-advanced-shipment-tracking' ); ?></span><i class="icon-angle-right"></i></a></p>
    </div>
    	
    <div class="zorem-sidebar__section">
    	<h3>More plugins by zorem</h3>
		<?php
			$response = wp_remote_get('https://www.zorem.com/wp-json/pluginlist/v1' );
			if ( is_array( $response ) ) {
			$plugin_list = json_decode($response['body']);		
		?>	
        <ul>
			<?php foreach($plugin_list as $plugin){ 
				if( 'Advanced Shipment Tracking for WooCommerce' != $plugin->title ) { 
			?>
				<li><img class="plugin_thumbnail" src="<?php echo $plugin->image_url; ?>"><a class="plugin_url" href="<?php echo $plugin->url; ?>" target="_blank"><?php echo $plugin->title; ?></a></li>
			<?php }
			}?>
        </ul>  
		<?php } ?>	
    </div>
</div>