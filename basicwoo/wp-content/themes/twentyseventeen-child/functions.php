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
	wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri() . '/js/custom-js.js', array('jquery'), '', true);
	wp_localize_script( 'custom-script', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	// slick js
	wp_enqueue_script( 'slick-script', get_stylesheet_directory_uri() . '/slick/slick.min.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'wpse_89494_enqueue_scripts', 99 );



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
		?>
        <div class="recent-product">
            <h3 class="recent-pro-head">Recent products</h3>
            <div class="slc-slider"><?php
    		while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
    				<div class="span3">
                            <?php 
                                if ( $product->get_sale_price() ) {
                                    echo '<img src="http://localhost/basicwoo/wp-content/uploads/2019/12/percent-badge.png" class="cslider-sale-badge" />';
                                }
                             ?>
    						<a id="id-<?php the_id(); ?>" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
    						<?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="My Image Placeholder" width="65px" height="115px" />'; ?>
    						<h3><?php the_title(); ?></h3>
    						<span class="price"><?php echo $product->get_price_html(); ?> /-</span>
    						</a>
    						<?php woocommerce_template_loop_add_to_cart( $loop->post, $product ); ?>
                            <div class="two-btn">
                                <?php 
                                    if($product->get_type() != "external"){
                                        ?>
                                            <div class="slider_add_to_cart">
                                                <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
                                            </div>
                                        <?php
                                    }
                                ?>
                                <div class="slider_wishlist">
                                    <?php echo do_shortcode( '[yith_wcwl_add_to_wishlist]' ); ?>
                                </div>
                            </div>
    				</div>
    		<?php endwhile; ?>
    		</div>
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
            '<div><label>%s</label><input type="text" id="pro_c_msg" name="pro_c_msg" value="" placeholder="%s" class="pro-cfield"></div><br/>',
            esc_html($custom_fields_woocommerce_title), esc_html($custom_fields_woocommerce_title),
      );
  	}
   	?>
   		<input type="text" name="custom_data_1" class="custom_data" id="custom_data_1" placeholder="Enter custom_data_1"><br/>
        <input type="text" name="custom_data_2" class="custom_data" id="custom_data_2" placeholder="Enter custom_data_2"><br/> 
   	<?php
} 
add_action('woocommerce_before_add_to_cart_button', 'woocommerce_custom_fields_display');


// Display custom field data with the Cart Item
function iconic_add_engraving_text_to_cart_item( $cart_item_meta, $product_id, $variation_id ) {
    $engraving_text = filter_input( INPUT_POST, 'pro_c_msg' );
 
 	$custom_data  = array() ;
    $custom_data['pro_c_msg'] = $engraving_text;
 	$cart_item_meta['custom_data'] = $custom_data['pro_c_msg'];

    return $cart_item_meta;
}
 
add_filter( 'woocommerce_add_cart_item_data', 'iconic_add_engraving_text_to_cart_item', 10, 3 );

// Display Data in the Cart
function iconic_display_engraving_text_cart( $item_data, $cart_item ) {
 	if ( isset( $cart_item [ 'custom_data' ] ) ) {
 		$custom_data  = $cart_item [ 'custom_data' ];
 		 		
 		$item_data[] = array(
	        'key'     => __( 'Text '),
	        'value'   =>  $custom_data,
	    );
 	}
 
    return $item_data;
} 
add_filter( 'woocommerce_get_item_data', 'iconic_display_engraving_text_cart', 10, 2 );


// Add order item meta in table and it' will display automatically in order page for both side
add_action( 'woocommerce_add_order_item_meta', 'add_order_item_meta' , 10, 2);
function add_order_item_meta ( $item_id, $values ) {
	if ( isset( $values [ 'custom_data' ] ) ) {
		$custom_data  = $values [ 'custom_data' ];
		wc_add_order_item_meta( $item_id, 'wooco_procfield_for_msg', $custom_data );
	}
}



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



/***************************************************************
9. How to Add Custom Data to WooCommerce Order
****************************************************************/
// step 1 : Add Data in a Custom Session, on 'Add to Cart' Button Click
add_action('wp_ajax_wdm_add_user_custom_data_options', 'wdm_add_user_custom_data_options_callback');
add_action('wp_ajax_nopriv_wdm_add_user_custom_data_options', 'wdm_add_user_custom_data_options_callback');
function wdm_add_user_custom_data_options_callback()
{
    session_start();
    $custom_data_ary = array();    
    // Is value set in first custom field?
    if(isset($_POST['custom_data_1'])){
    	array_push($custom_data_ary, $_POST['custom_data_1']);
	}
	// Is value set in second custom field?
	if(isset($_POST['custom_data_2'])){
    	array_push($custom_data_ary, $_POST['custom_data_2']);
	}
    // assign custom fields value into custom session
    $_SESSION['custom_data'] = $custom_data_ary;
    die();
}

// step 2 : Fetch custom session value and merge with cart session
add_filter('woocommerce_add_cart_item_data','wdm_add_item_data',1,2);
if(!function_exists('wdm_add_item_data'))
{
    function wdm_add_item_data($cart_item_data,$product_id)
    {
        /*Here, We are adding item in WooCommerce session with, wdm_user_custom_data_value name*/
        global $woocommerce;
        session_start();
        $new_value = array();
        if (isset($_SESSION['custom_data'][0])) {
            $option1 = $_SESSION['custom_data'][0];
            $new_value['custom_data_1'] =  $option1;
        }
        if (isset($_SESSION['custom_data'][1])) {
            $option2 = $_SESSION['custom_data'][1];
            $new_value['custom_data_2'] =  $option2;
        }
        if( empty($option1) && empty($option2) )
            return $cart_item_data;  // if custom field is empty, it's return cart value as it is
        else
        {
            if(empty($cart_item_data))
                return $new_value; // If cart session is empty then return only custom fields value
            else
                return array_merge($cart_item_data,$new_value); // If cart session have value then merge out custom session with Woo cart session
        }              
        unset($_SESSION['custom_data']); //Unset our custom session variable
    }
}

// step 3 : Display custom fieldds value on cart and checkout page with table format
add_filter('woocommerce_checkout_cart_item_quantity','wdm_add_user_custom_option_from_session_into_cart',1,3);
add_filter('woocommerce_cart_item_price','wdm_add_user_custom_option_from_session_into_cart',1,3);
if(!function_exists('wdm_add_user_custom_option_from_session_into_cart'))
{
    function wdm_add_user_custom_option_from_session_into_cart($product_name, $values, $cart_item_key )
    {
        if(isset($values['custom_data_1']) || isset($values['custom_data_2']))
        {
            $return_string = $product_name . "</a><dl class='variation'>";
            $return_string .= "<table class='wdm_options_table' id='" . $values['product_id'] . "'>";
            $return_string .= "<tr><td> custom_data_1 : " . $values['custom_data_1'] . "</td></tr>";
            $return_string .= "<tr><td> custom_data_2 : " . $values['custom_data_2'] . "</td></tr>";
            $return_string .= "</table></dl>";
            return $return_string;
        }
        else
        {
            return $product_name;
        }
    }
}

// step 4 : Add custom field value in order meta tabel, when order will place
add_action('woocommerce_add_order_item_meta','wdm_add_values_to_order_item_meta',1,2);
if(!function_exists('wdm_add_values_to_order_item_meta'))
{
    function wdm_add_values_to_order_item_meta($item_id, $values)
    {
        global $woocommerce,$wpdb;
        $custom_data_1 = $values['custom_data_1'];
        if(!empty($custom_data_1))
        {
            wc_add_order_item_meta($item_id,'custom_data_1',$custom_data_1);
        }
        $custom_data_2 = $values['custom_data_2'];
        if(!empty($custom_data_2))
        {
            wc_add_order_item_meta($item_id,'custom_data_2',$custom_data_2);
        }
    }
}


/**********************************************************
// 10. add to cart using ajax(without plugin)
 **********************************************************/
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');
        
function woocommerce_ajax_add_to_cart() {

    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
    $variation_id = absint($_POST['variation_id']);
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
    $product_status = get_post_status($product_id);

    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id) && 'publish' === $product_status) {

        do_action('woocommerce_ajax_added_to_cart', $product_id);

        if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
            wc_add_to_cart_message(array($product_id => $quantity), true);
        }

        WC_AJAX :: get_refreshed_fragments();
    } else {

        $data = array(
            'error' => true,
            'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));

        echo wp_send_json($data);
    }

    wp_die();
}


// 11. Add "add to cart" button on shop page
add_action('woocommerce_after_shop_loop_item','replace_add_to_cart');
function replace_add_to_cart() {
global $product;
?>
    <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
<?php
}

// 12. Add 'Sales' badge on product
add_filter('woocommerce_sale_flash', 'my_custom_sales_badge');
function my_custom_sales_badge() {
$img = '<img src="http://localhost/basicwoo/wp-content/uploads/2019/12/percent-badge.png" class="csale-badge" />';
return $img;
}


// 13. customize default product search widgets
add_filter( 'get_product_search_form' , 'woo_custom_product_searchform' );
function woo_custom_product_searchform( $form ) {
    $form = '<form role="search" method="get" id="searchform" action="' . esc_url( home_url( '/'  ) ) . '">
    <div>
      <label class="screen-reader-text" for="s">' . __( 'Search for:', 'woocommerce' ) . '</label>
      <input type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="' . __( ' Search products', 'woocommerce' ) . '" />
      <input type="submit" id="searchsubmit" value="'. esc_attr__( 'Search', 'woocommerce' ) .'" />
      <input type="hidden" name="post_type" value="product" />
    </div>
    </form>';
    return $form;
}


// 14. display product thumbnails vertical 
add_filter ( 'woocommerce_product_thumbnails_columns', 'bbloomer_change_gallery_columns' );
function bbloomer_change_gallery_columns() {
     return 1; 
}


// 15. display single product main image in full width 
function iconic_modify_theme_support() {
    $theme_support = get_theme_support( 'woocommerce' );
    $theme_support = is_array( $theme_support ) ? $theme_support[0] : array();

    unset( $theme_support['single_image_width'], $theme_support['thumbnail_image_width'] );

    remove_theme_support( 'woocommerce' );
    add_theme_support( 'woocommerce', $theme_support );
}
add_action( 'after_setup_theme', 'iconic_modify_theme_support', 10 );


//  display how many stock left
add_action( 'woocommerce_after_shop_loop_item', 'bbloomer_show_stock_shop', 10 );
  
function bbloomer_show_stock_shop() {
   global $product;
   echo wc_get_stock_html( $product );
}


//  16. Add custom footer widgets
function register_widget_areas() {

  register_sidebar( array(
    'name'          => 'Footer area one',
    'id'            => 'footer_area_one',
    'description'   => 'This widget area discription',
    'before_widget' => '<section class="footer-area footer-area-one">',
    'after_widget'  => '</section>',
    'before_title'  => '<h4>',
    'after_title'   => '</h4>',
  ));

  register_sidebar( array(
    'name'          => 'Footer area two',
    'id'            => 'footer_area_two',
    'description'   => 'This widget area discription',
    'before_widget' => '<section class="footer-area footer-area-two">',
    'after_widget'  => '</section>',
    'before_title'  => '<h4>',
    'after_title'   => '</h4>',
  ));

  register_sidebar( array(
    'name'          => 'Footer area three',
    'id'            => 'footer_area_three',
    'description'   => 'This widget area discription',
    'before_widget' => '<section class="footer-area footer-area-three">',
    'after_widget'  => '</section>',
    'before_title'  => '<h4>',
    'after_title'   => '</h4>',
  ));

  // register_sidebar( array(
  //   'name'          => 'Footer area four',
  //   'id'            => 'footer_area_four',
  //   'description'   => 'This widget area discription',
  //   'before_widget' => '<section class="footer-area footer-area-three">',
  //   'after_widget'  => '</section>',
  //   'before_title'  => '<h4>',
  //   'after_title'   => '</h4>',
  // ));

}

add_action( 'widgets_init', 'register_widget_areas' );


//  17. When user click on checkbox then additional charge will add on cart total
//  add checkbox after checkout form on checkout page
add_action( 'woocommerce_after_checkout_billing_form', 'add_box_option_to_checkout' );
function add_box_option_to_checkout( $checkout ) {
    echo '<div id="message_fields">';
    woocommerce_form_field( 'add_gift_box', array(
        'type'          => 'checkbox',
        'class'         => array('add_gift_box form-row-wide'),

        'label'         => __('Want into gift box?'),
        'placeholder'   => __(''),
        ), $checkout->get_value( 'add_gift_box' ));
        echo '</div>';
}

// refresh cart body
add_action( 'wp_footer', 'woocommerce_add_gift_box' );
function woocommerce_add_gift_box() {
    if (is_checkout()) {
    ?>
    <script type="text/javascript">
    jQuery( document ).ready(function( $ ) {
        $('#add_gift_box').click(function(){
            jQuery('body').trigger('update_checkout');
        });
    });
    </script>
    <?php
    }
}

// add twenty five rupees charge in woocommerce cart total
add_action( 'woocommerce_cart_calculate_fees', 'woo_add_cart_fee' );
function woo_add_cart_fee( $cart ){
    if ( ! $_POST || ( is_admin() && ! is_ajax() ) ) {
        return;
    }

    if ( isset( $_POST['post_data'] ) ) {
        parse_str( $_POST['post_data'], $post_data );
    } else {
        $post_data = $_POST; // fallback for final checkout (non-ajax)
    }

    if (isset($post_data['add_gift_box'])) {
        $extracost = 25; // not sure why you used intval($_POST['state']) ?
        WC()->cart->add_fee( 'Giftbox charges', $extracost );
    }

}

?>