<?php
/***************************************************
// 1. enqueue css and js file(child theme and CDN link)
***************************************************/
function wpse_89494_enqueue_scripts() {
	// CDN enqueue
	wp_enqueue_style('fontawsome-icon', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

	// slick css
	wp_enqueue_style('slick-css', get_stylesheet_directory_uri(). '/slick/slick.css');
	wp_enqueue_style('slick-css', get_stylesheet_directory_uri(). '/slick/slick-theme.css');

	// enqueue js
	wp_enqueue_script('jquery');
	wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri() . '/js/custom-js.js', array(), '1.0.0', true );

	// slick js
	wp_enqueue_script( 'slick-script', get_stylesheet_directory_uri() . '/slick/slick.min.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'wpse_89494_enqueue_scripts' );



/***************************************************
// 2. Add navigation menu
***************************************************/
function wpb_custom_new_menu() {
  register_nav_menus(
    array(
      'my-custom-menu' => __( 'My Custom Menu' ),
      'extra-menu' => __( 'Extra Menu' )
    )
  );
}
add_action( 'init', 'wpb_custom_new_menu' );



/***************************************************
// 3. How to create custom mini cart using shortcode?
***************************************************/
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



/***************************************************
// 4. How to display minicart product count using ajax?
***************************************************/
add_filter( 'woocommerce_add_to_cart_fragments', 'iconic_cart_count_fragments', 10, 1 ); 
function iconic_cart_count_fragments( $fragments ) {
    
    $fragments['div.header-cart-count'] = '<div class="header-cart-count">' . WC()->cart->get_cart_contents_count() . '</div>';
    
    return $fragments;
    
}



/***************************************************
// 5. show latest 10 products on home page with carousel slider without plugin(using slick slider)
***************************************************/
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


/***************************************************
// 6. How to admin product page custom data to WooCommerce Product Page(front end)
***************************************************/
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');
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
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');
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


/***************************************************
// 7. How to Add Custom Fields to WooCommerce Product Page
***************************************************/
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


// Display custom field data with the Cart Item
function iconic_add_engraving_text_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
    $engraving_text = filter_input( INPUT_POST, 'woocommerce_product_custom_fields_title' );
 
    if ( empty( $engraving_text ) ) {
        return $cart_item_data;
    }
 
    $cart_item_data['woocommerce_product_custom_fields_title'] = $engraving_text;
 
    return $cart_item_data;
}
 
add_filter( 'woocommerce_add_cart_item_data', 'iconic_add_engraving_text_to_cart_item', 10, 3 );


/***************************************************
// 8. How to Add Custom Fields to WooCommerce Checkout Page
// Add Custom Fields to WooCommerce Checkout Page
// How to pick data from custom field and add those data into WOO cart
// Concatenate data(If we want to show those data into front end. We must join those data with inbuilt output using filter.)
// Now, Data will show order page after placed order
// How to show data on my address page after placed order
// User will able to see and edit data on product page admin side
***************************************************/

// Step 1: Define an Array of Fields on Checkout Page
//global array to reposition the elements to display as you want (e.g. kept 'title' before 'first_name' )
$wdm_address_fields = array(
    'title', //new field
    'first_name',
    'last_name',
	'country',
    //'company',
    'address_1',
    'address_2',
    'city',
    'state',
    'postcode');

// Step 2: Add Custom Fields to WooCommerce Checkout Page
add_filter( 'woocommerce_default_address_fields' , 'wdm_override_default_address_fields' );
function wdm_override_default_address_fields( $address_fields ){    
	$temp_fields = array();
	$address_fields['title'] = array(
    'label'     => __('Title', 'woocommerce'),
    'required'  => true,
    'class'     => array('form-row-wide'),
    'type'  => 'select',
    'options'   => array('Mr' => __('Mr', 'woocommerce'), 'Mrs' => __('Mrs', 'woocommerce'), 'Miss' => __('Miss', 'woocommerce'))
     );

    global $wdm_address_fields;
    foreach($wdm_address_fields as $fky){  
       	$temp_fields[$fky] = $address_fields[$fky];
    }    
    $address_fields = $temp_fields;    
    return $address_fields;
}

// Step 3: Concatenate Fields as per Requirement
add_filter('woocommerce_formatted_address_replacements', 'wdm_formatted_address_replacements', 99, 2);
function wdm_formatted_address_replacements( $address, $args ){
    $address['{name}'] = $args['title']." ".$args['first_name']." ".$args['last_name']." ".$args['myname']; //show title along with name
    return $address;
}

// Step 4 : Display Custom Fields on Order Page
add_filter( 'woocommerce_order_formatted_billing_address', 'wdm_update_formatted_billing_address', 99, 2);
function wdm_update_formatted_billing_address( $address, $obj ){

    global $wdm_address_fields;
         
    if(is_array($wdm_address_fields)){
        foreach($wdm_address_fields as $waf){
            $address[$waf] = $obj->{'billing_'.$waf};
        }
    }         
    return $address;    
}
add_filter( 'woocommerce_order_formatted_shipping_address', 'wdm_update_formatted_shipping_address', 99, 2);
function wdm_update_formatted_shipping_address( $address, $obj ){

    global $wdm_address_fields;
         
    if(is_array($wdm_address_fields)){
        
        foreach($wdm_address_fields as $waf){
            $address[$waf] = $obj->{'shipping_'.$waf};
        }
    }   
    
    return $address;    
}

// Step 5: Display Fields on Account Page
add_filter('woocommerce_my_account_my_address_formatted_address', 'wdm_my_account_address_formatted_address', 99, 3);
function wdm_my_account_address_formatted_address( $address, $customer_id, $name ){
    global $wdm_address_fields;
    if(is_array($wdm_address_fields)){        
        foreach($wdm_address_fields as $waf){
            $address[$waf] = get_user_meta( $customer_id, $name.'_'.$waf, true );
        }
    }
    return $address;
}

// Step 6: Edit Option on Single Order Page
add_filter('woocommerce_admin_billing_fields', 'wdm_add_extra_customer_field');
add_filter('woocommerce_admin_shipping_fields', 'wdm_add_extra_customer_field');

function wdm_add_extra_customer_field( $fields ){

    //take back up of email and phone fields as they will be lost after repositioning
    $email = $fields['email']; 
    $phone = $fields['phone'];

    $fields = wdm_override_default_address_fields( $fields );
       
    //reassign email and phone fields
    $fields['email'] = $email;
    $fields['phone'] = $phone;
    
    return $fields;
}

?>