<?php
// enqueue css
function wpse_89494_enqueue_scripts() {
	wp_enqueue_style('fontawsome-icon', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

	// slick
	wp_enqueue_style('slick-css', get_stylesheet_directory_uri(). '/slick/slick.css');
	wp_enqueue_style('slick-css', get_stylesheet_directory_uri(). '/slick/slick-theme.css');
	// wp_enqueue_style('wpse_89494_style_1', get_template_directory_uri() . '/your-style_1.css');

	wp_enqueue_script('jquery');
	wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri() . '/js/custom-js.js', array(), '1.0.0', true );
	wp_enqueue_script( 'slick-script', get_stylesheet_directory_uri() . '/slick/slick.min.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'wpse_89494_enqueue_scripts' );


// navigation menu
function wpb_custom_new_menu() {
  register_nav_menus(
    array(
      'my-custom-menu' => __( 'My Custom Menu' ),
      'extra-menu' => __( 'Extra Menu' )
    )
  );
}
add_action( 'init', 'wpb_custom_new_menu' );

// Create custom mini cart using shortcode
function custom_mini_cart() { 
	echo '<div class="dropdown-back" data-toggle="dropdown"> ';
	echo '<i class="fa fa-shopping-cart" aria-hidden="true"></i>';
	echo '<div class="basket-item-count header-cart-count" style="display: inline;">';
	echo '<span class="cart-items-count count">';
	    echo WC()->cart->get_cart_contents_count();
	echo '</span>';
	echo '</div>';
	
	echo '<ul class="dropdown-menu dropdown-menu-mini-cart">';
	echo '<li> <div class="widget_shopping_cart_content">';
		woocommerce_mini_cart();
    echo '</div></li></ul>';

    echo '</div>';

}
add_shortcode( 'mini-cart-shocode', 'custom_mini_cart' );

// product count using ajax
add_filter( 'woocommerce_add_to_cart_fragments', 'iconic_cart_count_fragments', 10, 1 );
 
function iconic_cart_count_fragments( $fragments ) {
    
    $fragments['div.header-cart-count'] = '<div class="header-cart-count">' . WC()->cart->get_cart_contents_count() . '</div>';
    
    return $fragments;
    
}

// show latest 10 products on home page with carousel slider without plugin(using slick slider)
function display_lat_pro_carosel(){
	 ob_start();
    ?> 
    <?php
		$args = array(
		'post_type' => 'product',
		'stock' => 1,
		'posts_per_page' => 10,
		'orderby' =>'date',
		'order' => 'DESC' );
		$loop = new WP_Query( $args );
		?><div class="slc-slider"><?php
		while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
				<div class="span3">
						<a id="id-<?php the_id(); ?>" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
						<?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="My Image Placeholder" width="65px" height="115px" />'; ?>
						<h3><?php the_title(); ?></h3>
						<span class="price"><?php echo $product->get_price_html(); ?></span>
						</a>
						<?php woocommerce_template_loop_add_to_cart( $loop->post, $product ); ?>
				</div>
		<?php endwhile; ?>
		</div>				
		<?php wp_reset_query(); ?>
    <?php
    return ob_get_clean();
}
add_shortcode( 'show_lat_product_slider', 'display_lat_pro_carosel');


// Display Fields on admin side product page
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');
// Save Fields
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');
function woocommerce_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class="product_custom_field">';
    // Custom Product Text Field
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_text_field',
            'placeholder' => 'Custom Product Text Field',
            'label' => __('Custom Product Text Field', 'woocommerce'),
            'desc_tip' => 'true'
        )
    );
    //Custom Product Number Field
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_number_field',
            'placeholder' => 'Custom Product Number Field',
            'label' => __('Custom Product Number Field', 'woocommerce'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => 'any',
                'min' => '0'
            )
        )
    );
    //Custom Product  Textarea
    woocommerce_wp_textarea_input(
        array(
            'id' => '_custom_product_textarea',
            'placeholder' => 'Custom Product Textarea',
            'label' => __('Custom Product Textarea', 'woocommerce')
        )
    );
    echo '</div>';
}

// save product custom field
function woocommerce_product_custom_fields_save($post_id)
{
    // Custom Product Text Field
    $woocommerce_custom_product_text_field = $_POST['_custom_product_text_field'];
    if (!empty($woocommerce_custom_product_text_field))
        update_post_meta($post_id, '_custom_product_text_field', esc_attr($woocommerce_custom_product_text_field));
// Custom Product Number Field
    $woocommerce_custom_product_number_field = $_POST['_custom_product_number_field'];
    if (!empty($woocommerce_custom_product_number_field))
        update_post_meta($post_id, '_custom_product_number_field', esc_attr($woocommerce_custom_product_number_field));
// Custom Product Textarea Field
    $woocommerce_custom_procut_textarea = $_POST['_custom_product_textarea'];
    if (!empty($woocommerce_custom_procut_textarea))
        update_post_meta($post_id, '_custom_product_textarea', esc_html($woocommerce_custom_procut_textarea));
}


// display custom field on single product page
add_action( 'woocommerce_single_product_summary', 'bbloomer_show_sku_again_single_product', 40 );
 
function bbloomer_show_sku_again_single_product() {
   global $product;
   ?>
	<div class="product_meta">
		<?php 
		if(!empty(get_post_meta(get_the_ID(), '_custom_product_text_field', true))){
			?>
				<h2 class="pro-cust-head">Additional Fields</h2>
			<?php
		}
		?>
		<!-- Display the value of custom product text field -->
		<span class="pro-cfield"><?php echo get_post_meta(get_the_ID(), '_custom_product_text_field', true); ?></span>
		<!-- Display the value of custom product number field -->
		<span class="pro-cfield"><?php echo get_post_meta(get_the_ID(), '_custom_product_number_field', true); ?></span>
		<!-- Display the value of custom product text area -->
		<span class="pro-cfield" ><?php echo get_post_meta(get_the_ID(), '_custom_product_textarea', true); ?></span>
    </div>
    <?php
}


// Add WooCommerce Custom Fields on Product Page
function woocommerce_product_custom_fields1()
{
  $args = array(
      'id' => 'woocommerce_custom_fields',
      'label' => __('Add WooCommerce Custom Fields', 'cwoa'),
  );
  woocommerce_wp_text_input($args);
} 
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields1');


// Save Custom Fields WooCommerce
function save_woocommerce_product_custom_fields1($post_id)
{
    $product = wc_get_product($post_id);
    $custom_fields_woocommerce_title = isset($_POST['woocommerce_custom_fields']) ? $_POST['woocommerce_custom_fields'] : '';
    $product->update_meta_data('woocommerce_custom_fields', sanitize_text_field($custom_fields_woocommerce_title));
    $product->save();
}
add_action('woocommerce_process_product_meta', 'save_woocommerce_product_custom_fields1');


// WooCommerce Display Custom Fields On Product Page
function woocommerce_custom_fields_display()
{
  global $post;
  $product = wc_get_product($post->ID);
    $custom_fields_woocommerce_title = $product->get_meta('woocommerce_custom_fields');
  if ($custom_fields_woocommerce_title) {
      printf(
            '<div><label>%s</label><input type="text" id="woocommerce_product_custom_fields_title" name="woocommerce_product_custom_fields_title" value="" placeholder="%s" class="pro-c-input"></div><br/>',
            esc_html($custom_fields_woocommerce_title), esc_html($custom_fields_woocommerce_title),
      );
  }
} 
add_action('woocommerce_before_add_to_cart_button', 'woocommerce_custom_fields_display');


// Add Data to the Cart Item
function iconic_add_engraving_text_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
    $engraving_text = filter_input( INPUT_POST, 'woocommerce_product_custom_fields_title' );
 
    if ( empty( $engraving_text ) ) {
        return $cart_item_data;
    }
 
    $cart_item_data['woocommerce_product_custom_fields_title'] = $engraving_text;
 
    return $cart_item_data;
}
 
add_filter( 'woocommerce_add_cart_item_data', 'iconic_add_engraving_text_to_cart_item', 10, 3 );


// Display Data in the Cart
function iconic_display_engraving_text_cart( $item_data, $cart_item ) {
	if ( empty( $cart_item['woocommerce_product_custom_fields_title'] ) ) {
        return $item_data;
    }
 
    $item_data[] = array(
        'key'     => __( 'Text '),
        'value'   =>  $cart_item['woocommerce_product_custom_fields_title'],
    );
 
    return $item_data;
} 
add_filter( 'woocommerce_get_item_data', 'iconic_display_engraving_text_cart', 10, 2 );

?>