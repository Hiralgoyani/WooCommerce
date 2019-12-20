<?php
/**
 * html code for admin sidebar
 */
?>
<div class="zorem_admin_sidebar">
	<div class="ts_launch zorem-sidebar__section">                    	
        <h3>Your opinion matters to us!</h3>
		<p>If you enjoy using advanced shipment tracking plugin, please take a minute to review the plugin</br>
		<span>Thanks :)</span>
		</p>						
        <a href="https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/reviews/#new-post" class="button button-primary btn_ast1 btn_large" target="_blank"><span>Share your review >></span><i class="icon-angle-right"></i></a>
    </div>
    
	<?php 
	$wc_ast_api_key = get_option('wc_ast_api_key');
	
	if(!$wc_ast_api_key){
	?>
		<div class="ts_launch zorem-sidebar__section">                    	
			<h3 style="padding: 12px 15px 7px;"><img src="https://trackship.info/wp-content/uploads/2019/08/trackship-400.png" class="sidebar_ts_logo" style="max-width: 60%;"></h3>
			<p>TrackShip supports 100+ carriers and seamlessly integrates with WooCommerce to automate your post shipping operations.</br>
			</p>						
			<a href="https://trackship.info/?utm_source=wpadmin&utm_campaign=astsidebar" class="button button-primary btn_green2 btn_large" target="_blank"><span>Try TrackShip for free!</span><i class="icon-angle-right"></i></a>
		</div>
	<?php } ?>
		
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