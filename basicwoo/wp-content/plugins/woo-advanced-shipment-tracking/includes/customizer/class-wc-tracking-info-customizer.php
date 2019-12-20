<?php
/**
 * Customizer Setup and Custom Controls
 *
 */

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class wcast_initialise_customizer_settings {
	// Get our default values	
	private static $order_ids  = null;
	
	public function __construct() {
		// Get our Customizer defaults
		$this->defaults = $this->wcast_generate_defaults();		
		
		// Register our sample default controls
		add_action( 'customize_register', array( $this, 'wcast_register_sample_default_controls' ) );
		
		// Only proceed if this is own request.
		if ( ! wcast_initialise_customizer_settings::is_own_customizer_request() && ! wcast_initialise_customizer_settings::is_own_preview_request() ) {
			return;
		}		
		
		// Register our Panels
		add_action( 'customize_register', array( wcast_customizer(), 'wcast_add_customizer_panels' ) );

		// Register our sections
		add_action( 'customize_register', array( wcast_customizer(), 'wcast_add_customizer_sections' ) );	
		
		// Remove unrelated components.
		add_filter( 'customize_loaded_components', array( wcast_customizer(), 'remove_unrelated_components' ), 99, 2 );

		// Remove unrelated sections.
		add_filter( 'customize_section_active', array( wcast_customizer(), 'remove_unrelated_sections' ), 10, 2 );	
		
		// Unhook divi front end.
		add_action( 'woomail_footer', array( wcast_customizer(), 'unhook_divi' ), 10 );

		// Unhook Flatsome js
		add_action( 'customize_preview_init', array( wcast_customizer(), 'unhook_flatsome' ), 50  );
		
		add_filter( 'customize_controls_enqueue_scripts', array( wcast_customizer(), 'enqueue_customizer_scripts' ) );				
		
		add_action( 'parse_request', array( $this, 'set_up_preview' ) );	
		
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );			
	}
	
	/**
	 * add css and js for preview
	*/	
	public function enqueue_preview_scripts() {
		 wp_enqueue_script('wcast-preview-scripts', wc_advanced_shipment_tracking()->plugin_dir_url() . '/assets/js/preview-scripts.js', array('jquery', 'customize-preview'), wc_advanced_shipment_tracking()->version, true);
		 wp_enqueue_style('wcast-preview-styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/preview-styles.css', array(), wc_advanced_shipment_tracking()->version  );
		 $preview_id     = get_theme_mod('wcast_email_preview_order_id');
		 wp_localize_script('wcast-preview-scripts', 'wcast_preview', array(
			'site_title'   => $this->get_blogname(),
			'order_number' => $preview_id,			
		));
	}
	
	/**
	* Get blog name formatted for emails.
	*
	* @return string
	*/
	public function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}
	
	/**
	 * Checks to see if we are opening our custom customizer preview
	 *
	 * @access public
	 * @return bool
	 */
	public static function is_own_preview_request() {
		return isset( $_REQUEST['wcast-tracking-preview'] ) && '1' === $_REQUEST['wcast-tracking-preview'];
	}
	
	/**
	 * Checks to see if we are opening our custom customizer controls
	 *
	 * @access public
	 * @return bool
	 */
	public static function is_own_customizer_request() {
		return isset( $_REQUEST['email'] ) && $_REQUEST['email'] === 'default_controls_section';
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_customizer_url($email,$return_tab) {	
			//echo $return_tab;exit;
			$customizer_url = add_query_arg( array(
				'wcast-customizer' => '1',
				'email' => $email,
				'url'                  => urlencode( add_query_arg( array( 'wcast-tracking-preview' => '1' ), home_url( '/' ) ) ),
				'return'               => urlencode( wcast_initialise_customizer_settings::get_email_settings_page_url($return_tab) ),
			), admin_url( 'customize.php' ) );		

		return $customizer_url;
	}
	
	/**
	 * Get WooCommerce email settings page URL
	 *
	 * @access public
	 * @return string
	 */
	public static function get_email_settings_page_url($return_tab) {
		return admin_url( 'admin.php?page=woocommerce-advanced-shipment-tracking&tab='.$return_tab );
	}
	
	/**
	 * code for initialize default value for customizer
	*/	
	public function wcast_generate_defaults() {
		$customizer_defaults = array(
			'display_shipment_provider_image' => '',
			'remove_date_from_tracking' => '',
			'header_text_change' => '',
			'additional_header_text' => '',
			'table_bg_color' => '',
			'table_border_color' => '',
			'table_border_size' => '',
			'table_header_font_size' => '',
			'table_header_font_color' => '',
			'table_content_font_size' => '',
			'table_content_font_color' => '',
			'tracking_link_font_color' => '',
			'tracking_link_bg_color' => '',	
			'wcast_preview_order_id' => 'mockup',
			'table_content_line_height' => '20',
			'table_content_font_weight' => '100',
			'table_padding'  => '12',
			'header_content_text_align'  => 'left',
			'tracking_link_border' => 1,
			'show_track_label' => '',
			'provider_header_text' => __( 'Provider', 'woo-advanced-shipment-tracking' ),
			'tracking_number_header_text' => __( 'Tracking Number', 'woo-advanced-shipment-tracking' ),
			'shipped_date_header_text' => __( 'Shipped Date', 'woo-advanced-shipment-tracking' ),
			'track_header_text' => __( 'Track', 'woo-advanced-shipment-tracking' ),
			'display_tracking_info_at' => 'before_order',
		);

		return apply_filters( 'skyrocket_customizer_defaults', $customizer_defaults );
	}	
	
	/**
	 * Register our sample default controls
	 */
	public function wcast_register_sample_default_controls( $wp_customize ) {		
		/**
		* Load all our Customizer Custom Controls
		*/
		require_once trailingslashit( dirname(__FILE__) ) . 'custom-controls.php';
		
		$font_size_array[ '' ] = __( 'Select', 'woocommerce' );
		for ( $i = 10; $i <= 30; $i++ ) {
			$font_size_array[ $i ] = $i."px";
		}
		
		// Preview Order				
		$wp_customize->add_setting( 'wcast_preview_order_id',
			array(
				'default' => $this->defaults['wcast_preview_order_id'],
				'transport' => 'refresh',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new Skyrocket_Dropdown_Select_Custom_Control( $wp_customize, 'wcast_preview_order_id',
			array(
				'label' => __( 'Preview order', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( 'select from last 20 orders one order that you added tracking info in order to preview and design the tracking info table.', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'input_attrs' => array(
					'placeholder' => __( 'Please select a order...', 'woo-advanced-shipment-tracking' ),
					'class' => 'preview_order_select',
				),
				'choices' => wcast_customizer()->get_order_ids(),
			)
		) );	
				
		$wp_customize->add_setting( 'general_settings_header',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'general_settings_header',
			array(
				'label' => __( 'General Settings', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section'
			)
		) );
		
		// Tracking Display Position
		$wp_customize->add_setting( 'display_tracking_info_at',
			array(
				'default' => $this->defaults['display_tracking_info_at'],
				'transport' => 'refresh',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'display_tracking_info_at',
			array(
				'label' => __( 'Tracking Display Position', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'select',
				'choices' => array(					
					'before_order'		=> __( 'Before Order Details', 'woo-advanced-shipment-tracking' ),
					'after_order'		=> __( 'After Order Details', 'woo-advanced-shipment-tracking' ),							
				)
			)
		);	
		
		// Header Text		
		$wp_customize->add_setting( 'header_text_change',
			array(
				'default' => $this->defaults['header_text_change'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'header_text_change',
			array(
				'label' => __( 'Main Header text', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Tracking Information', 'woo-advanced-shipment-tracking' ),
				),
			)
		);
		
		// Additional text after header
		$wp_customize->add_setting( 'additional_header_text',
			array(
				'default' => $this->defaults['additional_header_text'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'additional_header_text',
			array(
				'label' => __( 'Additional text after header', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'textarea',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' =>'',
				),
			)
		);
		
		// Test of Toggle Switch Custom Control
		$wp_customize->add_setting( 'table_content_header',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'table_content_header',
			array(
				'label' => __( 'Table Content', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section'
			)
		) );
		
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'display_shipment_provider_image',
			array(
				'default' => $this->defaults['display_shipment_provider_image'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'display_shipment_provider_image',
			array(
				'label' => __( 'Display Shipment Provider image', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'checkbox'
			)
		);
		
		// Remove date from tracking info
		$wp_customize->add_setting( 'remove_date_from_tracking',
			array(
				'default' => $this->defaults['remove_date_from_tracking'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'remove_date_from_tracking',
			array(
				'label' => __( 'Hide date', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'checkbox'
			)
		);
		
		// Test of Toggle Switch Custom Control
		$wp_customize->add_setting( 'table_header_block',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'table_header_block',
			array(
				'label' => __( 'Table Header', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section'
			)
		) );
		
		// Show track label
		$wp_customize->add_setting( 'hide_table_header',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'hide_table_header',
			array(
				'label' => __( 'Hide Header', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'checkbox'
			)
		);
		
		// Provider Header Text		
		$wp_customize->add_setting( 'provider_header_text',
			array(
				'default' => $this->defaults['provider_header_text'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'provider_header_text',
			array(
				'label' => __( 'Provider Header Text', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Provider', 'woo-advanced-shipment-tracking' ),
				),
			)
		);
		
		// Tracking Number Header Text		
		$wp_customize->add_setting( 'tracking_number_header_text',
			array(
				'default' => $this->defaults['tracking_number_header_text'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'tracking_number_header_text',
			array(
				'label' => __( 'Tracking Number Header Text', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Tracking Number', 'woo-advanced-shipment-tracking' ),
				),
			)
		);
		// Shipped Date Header Text		
		$wp_customize->add_setting( 'shipped_date_header_text',
			array(
				'default' => $this->defaults['shipped_date_header_text'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'shipped_date_header_text',
			array(
				'label' => __( 'Shipped Date Header Text', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Shipped Date', 'woo-advanced-shipment-tracking' ),
				),
			)
		);		
		// Show track label
		$wp_customize->add_setting( 'show_track_label',
			array(
				'default' => $this->defaults['show_track_label'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'show_track_label',
			array(
				'label' => __( 'Track Label', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'checkbox'
			)
		);										
		// Track Header Text		
		$wp_customize->add_setting( 'track_header_text',
			array(
				'default' => $this->defaults['track_header_text'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'track_header_text',
			array(
				'label' => __( 'Track Header Text', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Track', 'woo-advanced-shipment-tracking' ),
				),
			)
		);		
		
		// Table header font size
		$wp_customize->add_setting( 'table_header_font_size',
			array(
				'default' => $this->defaults['table_header_font_size'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'table_header_font_size',
			array(
				'label' => __( 'Table header font size', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'select',
				'choices' => $font_size_array
			)
		);				
		
		// Table header font color
		$wp_customize->add_setting( 'table_header_font_color',
			array(
				'default' => $this->defaults['table_header_font_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'table_header_font_color',
			array(
				'label' => __( 'Table header font color', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'color'
			)
		);
		
		// Test of Toggle Switch Custom Control
		$wp_customize->add_setting( 'table_header',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'table_header',
			array(
				'label' => __( 'Table Design', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section'
			)
		) );
		
		// Table content font weight
		$wp_customize->add_setting( 'table_padding',
			array(
				'default' => $this->defaults['table_padding'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new Skyrocket_Slider_Custom_Control( $wp_customize, 'table_padding',
			array(
				'label' => __( 'Padding', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'input_attrs' => array(
						'default' => $this->defaults['table_padding'],
						'step'  => 1,
						'min'   => 5,
						'max'   => 30,
					),
			)
		));
		// Table Background color
		$wp_customize->add_setting( 'table_bg_color',
			array(
				'default' => $this->defaults['table_bg_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color'
			)
		);
		$wp_customize->add_control( 'table_bg_color',
			array(
				'label' => __( 'Background color', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'color',				
			)
		);
	/*	$wp_customize->add_control( new Skyrocket_Customize_Alpha_Color_Control( $wp_customize, 'table_bg_color',
			array(
				'label' => __( 'Content font weight', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'input_attrs' => array(
						'default' => $this->defaults['table_bg_color'],
						'step'  => 100,
						'min'   => 100,
						'max'   => 900,
					),
			)
		));*/
		
		// Table Border color
		$wp_customize->add_setting( 'table_border_color',
			array(
				'default' => $this->defaults['table_border_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color'
			)
		);
		$wp_customize->add_control( 'table_border_color',
			array(
				'label' => __( 'Border color', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'color'
			)
		);
		
		// Table Border size
		$wp_customize->add_setting( 'table_border_size',
			array(
				'default' => $this->defaults['table_border_size'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'table_border_size',
			array(
				'label' => __( 'Border size', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'select',
				'choices' => array(
					'' => __( 'Select', 'woocommerce' ),
					'1'		=> '1 px',
					'2'		=> '2 px',
					'3'		=> '3 px',
					'4'		=> '4 px',
					'5'		=> '5 px',
				)
			)
		);
		
		// Table Border size
		$wp_customize->add_setting( 'header_content_text_align',
			array(
				'default' => $this->defaults['header_content_text_align'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'header_content_text_align',
			array(
				'label' => __( 'Table text align', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'select',
				'choices' => array(
					'' => __( 'Select', 'woocommerce' ),
					'left'		=> __( 'Left', '' ),
					'right'		=> __( 'Right', '' ),
					'center'	=> __( 'Center', '' )
				)
			)
		);	
		
		
		// Table content font size
		$wp_customize->add_setting( 'table_content_font_size',
			array(
				'default' => $this->defaults['table_content_font_size'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'table_content_font_size',
			array(
				'label' => __( 'Table content font size', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'select',
				'choices' => $font_size_array
			)
		);		
		
		// Table content font color
		$wp_customize->add_setting( 'table_content_font_color',
			array(
				'default' => $this->defaults['table_content_font_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'table_content_font_color',
			array(
				'label' => __( 'Table content font color', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'color'
			)
		);
		
		// Table content line height
		$wp_customize->add_setting( 'table_content_line_height',
			array(
				'default' => $this->defaults['table_content_line_height'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new Skyrocket_Slider_Custom_Control( $wp_customize, 'table_content_line_height',
			array(
				'label' => __( 'Content line height', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'input_attrs' => array(
						'default' => $this->defaults['table_content_line_height'],
						'step'  => 1,
						'min'   => 20,
						'max'   => 90,
					),
			)
		));
		
		// Table content font weight
		$wp_customize->add_setting( 'table_content_font_weight',
			array(
				'default' => $this->defaults['table_content_font_weight'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new Skyrocket_Slider_Custom_Control( $wp_customize, 'table_content_font_weight',
			array(
				'label' => __( 'Content font weight', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'input_attrs' => array(
						'default' => $this->defaults['table_content_font_weight'],
						'step'  => 100,
						'min'   => 100,
						'max'   => 900,
					),
			)
		));
		
		$wp_customize->add_setting( 'shipment_link_header',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		
		
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'shipment_link_header',
			array(
				'label' => __( 'Track Button Design', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section'
			)
		) );
		// Tracking link font color
		$wp_customize->add_setting( 'tracking_link_font_color',
			array(
				'default' => $this->defaults['tracking_link_font_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'tracking_link_font_color',
			array(
				'label' => __( 'Track Link Font Color', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'color'
			)
		);

		// Tracking link background color
		$wp_customize->add_setting( 'tracking_link_bg_color',
			array(
				'default' => $this->defaults['tracking_link_bg_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'tracking_link_bg_color',
			array(
				'label' => __( 'Track Link Background Color', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'color'
			)
		);	
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'tracking_link_border',
			array(
				'default' => $this->defaults['tracking_link_border'],
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'tracking_link_border',
			array(
				'label' => __( 'Track link Border', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'default_controls_section',
				'type' => 'checkbox'
			)
		);	
	}	
	
	/**
	 * Set up preview
	 *
	 * @access public
	 * @return void
	 */
	public function set_up_preview() {
		
		// Make sure this is own preview request.
		if ( ! wcast_initialise_customizer_settings::is_own_preview_request() ) {
			return;
		}
		include wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/preview/preview.php';		
		exit;			
	}
	
	/**
	 * code for preview of tracking info in email
	*/	
	public function preview_completed_email(){
		
		$ast = new WC_Advanced_Shipment_Tracking_Actions;				
		
		$display_tracking_info_at = get_theme_mod('display_tracking_info_at','before_order');		
					
		if($display_tracking_info_at == 'after_order'){			
			add_action( 'woocommerce_email_order_meta', array( $ast, 'email_display' ), 0, 4 );
		} else{
			add_action( 'woocommerce_email_before_order_table', array( $ast, 'email_display' ), 0, 4 );
		}	
		
		// Load WooCommerce emails.
		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();
		$email_template = 'customer_completed_order';
		$preview_id     = get_theme_mod('wcast_preview_order_id');
		$email_type = 'WC_Email_Customer_Completed_Order';
		if ( false === $email_type ) {
			return false;
		}

		$order_status = 'completed';
		
		if($preview_id == '' || $preview_id == 'mockup') {
			$content = '<div style="padding: 35px 40px; background-color: white;">' . __( 'Please select order to preview.', 'woo-advanced-shipment-tracking' ) . '</div>';							
			echo $content;
			return;
		}		
		
		// Reference email.
		if ( isset( $emails[ $email_type ] ) && is_object( $emails[ $email_type ] ) ) {
			$email = $emails[ $email_type ];
		}
		
		// Get an order
		$order               = self::get_wc_order_for_preview( $order_status, $preview_id );		

		// Make sure gateways are running in case the email needs to input content from them.
		WC()->payment_gateways();
		// Make sure shipping is running in case the email needs to input content from it.
		WC()->shipping();
			
		$email->object               = $order;
		$email->find['order-date']   = '{order_date}';
		$email->find['order-number'] = '{order_number}';
		if ( is_object( $order ) ) {
			$email->replace['order-date']   = wc_format_datetime( $email->object->get_date_created() );
			$email->replace['order-number'] = $email->object->get_order_number();
			// Other properties
			$email->recipient = $email->object->get_billing_email();
		}
		// Get email content and apply styles.
		$content = $email->get_content();
		$content = $email->style_inline( $content );
		$content = apply_filters( 'woocommerce_mail_content', $content );

		if ( 'plain' === $email->email_type ) {
			$content = '<div style="padding: 35px 40px; background-color: white;">' . str_replace( "\n", '<br/>', $content ) . '</div>';
		}
		echo $content;	
	}	
	
	/**
	 * Get WooCommerce order for preview
	 *
	 * @access public
	 * @param string $order_status
	 * @return object
	 */
	public static function get_wc_order_for_preview( $order_status = null, $order_id = null ) {
		if ( ! empty( $order_id ) && 'mockup' != $order_id ) {
			return wc_get_order( $order_id );
		} else {			

			// Instantiate order object
			$order = new WC_Order();

			// Other order properties
			$order->set_props( array(
				'id'                 => 1,
				'status'             => ( null === $order_status ? 'processing' : $order_status ),
				'billing_first_name' => 'Sherlock',
				'billing_last_name'  => 'Holmes',
				'billing_company'    => 'Detectives Ltd.',
				'billing_address_1'  => '221B Baker Street',
				'billing_city'       => 'London',
				'billing_postcode'   => 'NW1 6XE',
				'billing_country'    => 'GB',
				'billing_email'      => 'sherlock@holmes.co.uk',
				'billing_phone'      => '02079304832',
				'date_created'       => date( 'Y-m-d H:i:s' ),
				'total'              => 24.90,
			) );

			// Item #1
			$order_item = new WC_Order_Item_Product();
			$order_item->set_props( array(
				'name'     => 'A Study in Scarlet',
				'subtotal' => '9.95',
				'sku'      => 'kwd_ex_1',
			) );
			$order->add_item( $order_item );

			// Item #2
			$order_item = new WC_Order_Item_Product();
			$order_item->set_props( array(
				'name'     => 'The Hound of the Baskervilles',
				'subtotal' => '14.95',
				'sku'      => 'kwd_ex_2',
			) );
			$order->add_item( $order_item );

			// Return mockup order
			return $order;
		}
	}
}
/**
 * Initialise our Customizer settings
 */

$wcast_customizer_settings = new wcast_initialise_customizer_settings();