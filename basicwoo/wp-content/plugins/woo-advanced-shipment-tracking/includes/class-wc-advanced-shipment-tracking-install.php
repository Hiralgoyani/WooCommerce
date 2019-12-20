<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Install {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	*/
    public function __construct() {
		
		global $wpdb;
		$this->table = $wpdb->prefix."woo_shippment_provider";
		if( is_multisite() ){
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if ( is_plugin_active_for_network( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' ) ) {
				$main_blog_prefix = $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE);			
				$this->table = $main_blog_prefix."woo_shippment_provider";	
			} else{
				$this->table = $wpdb->prefix."woo_shippment_provider";
			}			
		} else{
			$this->table = $wpdb->prefix."woo_shippment_provider";	
		}
		
		$this->init();	
    }
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Install
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init(){				
		add_action( 'init', array( $this, 'update_database_check'));			
		add_action( 'wp_ajax_ast_hide_admin_menu_tooltip', array( $this, 'ast_mark_admin_menu_tooltip_hidden') );		
		$wc_ast_api_key = get_option('wc_ast_api_key');		
		
		require_once( 'vendor/persist-admin-notices-dismissal/persist-admin-notices-dismissal.php' );			
		add_action( 'admin_init', array( 'PAnD', 'init' ) );
		
		if ( !is_plugin_active( 'ast-tracking-per-order-items/ast-tracking-per-order-items.php' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_for_addon' ) );
		}
		
		if(!$wc_ast_api_key){
			add_action( 'adminmenu', array( $this, 'admin_notice_menu_tooltip') );				
			//add_action( 'admin_notices', array( $this, 'admin_notice_after_update' ) );	
		}
	}	

	/**
	 * Define plugin activation function
	 *
	 * Create Table
	 *
	 * Insert data 
	 *
	 * 
	*/	
	public function woo_shippment_tracking_install(){
		
		global $wpdb;	
		
		// Add transient to trigger redirect.
		set_transient( '_ast_activation_redirect', 1, 30 );
		
		$woo_shippment_table_name = $wpdb->prefix . 'woo_shippment_provider';
		$woo_shippment_status_email_table = $wpdb->prefix . 'woo_shipment_status_email';
		// create the ECPT metabox database table
		if($wpdb->get_var("show tables like '$woo_shippment_status_email_table'") != $woo_shippment_status_email_table) {
			$charset_collate = $wpdb->get_charset_collate();
			
			$sql = "CREATE TABLE $woo_shippment_status_email_table (
				id int(11) NOT NULL AUTO_INCREMENT,
				enable int(11) NOT NULL DEFAULT '1',
				email_trigger text NOT NULL,
				email_label text NOT NULL,
				email_to text NOT NULL,
				shippment_status text NOT NULL,
				order_status text NOT NULL,
				email_subject text NOT NULL,
				email_heading text NOT NULL,
				email_content text NOT NULL,				
				PRIMARY KEY  (id)
			) $charset_collate;";
			//echo $sql;exit;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
		if($wpdb->get_var("show tables like '$woo_shippment_table_name'") != $woo_shippment_table_name) 
		{
			$charset_collate = $wpdb->get_charset_collate();
			
			$sql = "CREATE TABLE $woo_shippment_table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				provider_name varchar(500) DEFAULT '' NOT NULL,
				ts_slug text NULL DEFAULT NULL,
				provider_url varchar(500) DEFAULT '' NOT NULL,
				shipping_country varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
				shipping_default tinyint(4) NOT NULL DEFAULT '0',
				custom_thumb_id int(11) NOT NULL DEFAULT '0',
				display_in_order tinyint(4) NOT NULL DEFAULT '1',
				sort_order int(11) NOT NULL DEFAULT '0',				
				PRIMARY KEY  (id)
			) $charset_collate;";
			//echo $sql;exit;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
						
			$providers = $this->provider_list();
			
			foreach($providers as $shipping_provider){				
				$shipping_provider['provider_name'];
				$success = $wpdb->insert($woo_shippment_table_name, array(
					"provider_name" => $shipping_provider['provider_name'],
					"ts_slug" => $shipping_provider['ts_slug'],
					"provider_url" => $shipping_provider['provider_url'],
					"shipping_country" => $shipping_provider['shipping_country'],
					"shipping_default" => $shipping_provider['shipping_default'],
				));
			}
			update_option( 'wc_advanced_shipment_tracking', '3.1');	
		}			
	}
	
	/**
	 *	
	 * Return shipment provider list array
	 * 
	*/
	public function provider_list(){
		$providers = array( 
			0 => array (
				"provider_name" => 'Australia Post',				
				"ts_slug" => 'australia-post',
				"provider_url" => 'http://auspost.com.au/track/track.html?id=%number%',
				"shipping_country" => 'AU',
				"shipping_default" => '1'
			),
			
			1 => array (
				"provider_name" => 'Fastway AU',
				"ts_slug" => 'fastway-au',
				"provider_url" => 'http://www.fastway.com.au/courier-services/track-your-parcel?l=%number%',
				"shipping_country" => 'AU',
				"shipping_default" => '1'
			),
			
			2 => array (
				"provider_name" => 'post.at',
				"ts_slug" => 'post-at',
				"provider_url" => 'http://www.post.at/sendungsverfolgung.php?pnum1=%number%',
				"shipping_country" => 'AT',
				"shipping_default" => '1'
			),
				
			3 => array (
				"provider_name" => 'DHL at',
				"ts_slug" => 'dhl-at',
				"provider_url" => 'http://www.dhl.at/content/at/de/express/sendungsverfolgung.html?brand=DHL&AWB=%number%',
				"shipping_country" => 'AT',
				"shipping_default" => '1'
			),

			4 => array (
				"provider_name" => 'DPD.at',
				"ts_slug" => 'dpd-at',
				"provider_url" => 'https://tracking.dpd.de/parcelstatus?locale=de_AT&query=%number%',
				"shipping_country" => 'AT',
				"shipping_default" => '1'
			),

			5 => array (
				"provider_name" => 'Brazil Correios',
				"ts_slug" => 'brazil-correios',
				"provider_url" => 'http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=%number%',
				"shipping_country" => 'BR',
				"shipping_default" => '1'
			),

			6 => array (
				"provider_name" => 'Belgium Post',
				"ts_slug" => 'belgium-post',
				"provider_url" => 'https://track.bpost.be/btr/web/#/search?itemCode=%number%',
				"shipping_country" => 'BE',
				"shipping_default" => '1'
			),

			7 => array (
				"provider_name" => 'Canada Post',
				"ts_slug" => 'canada-post',
				"provider_url" => 'http://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber=%number%',
				"shipping_country" => 'CA',
				"shipping_default" => '1'
			),
			
			8 => array (
				"provider_name" => 'DHL cz',
				"ts_slug" => 'dhl-cz',
				"provider_url" => 'http://www.dhl.cz/cs/express/sledovani_zasilek.html?AWB=%number%',
				"shipping_country" => 'CZ',
				"shipping_default" => '1'
			),
			
			9 => array (
				"provider_name" => 'DPD.cz',
				"ts_slug" => 'dpd-cz',
				"provider_url" => 'https://tracking.dpd.de/parcelstatus?locale=cs_CZ&query=%number%',
				"shipping_country" => 'CZ',
				"shipping_default" => '1'
			),

			10 => array (
				"provider_name" => 'Colissimo',
				"ts_slug" => 'colissimo',
				"provider_url" => 'https://www.laposte.fr/outils/suivre-vos-envois?code=%number%',
				"shipping_country" => 'FR',
				"shipping_default" => '1'
			),

			11 => array (
				"provider_name" => 'DHL Intraship (DE)',
				"ts_slug" => 'dhl-intraship-de',
				"provider_url" => 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=%number%&rfn=&extendedSearch=true',
				"shipping_country" => 'DE',
				"shipping_default" => '1'
			),

			12 => array (
				"provider_name" => 'Hermes Germany',
				"ts_slug" => 'hermes-de',
				"provider_url" => 'https://www.myhermes.de/empfangen/sendungsverfolgung/?suche=%number%',
				"shipping_country" => 'DE',
				"shipping_default" => '1'
			),

			13 => array (
				"provider_name" => 'Deutsche Post DHL',
				"ts_slug" => 'deutsche-post-dhl',
				"provider_url" => 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=%number%',
				"shipping_country" => 'DE',
				"shipping_default" => '1'
			),

			14 => array (
				"provider_name" => 'UPS Germany',
				"ts_slug" => 'ups-germany',
				"provider_url" => 'http://wwwapps.ups.com/WebTracking/processInputRequest?sort_by=status&tracknums_displayed=1&TypeOfInquiryNumber=T&loc=de_DE&InquiryNumber1=%number%',
				"shipping_country" => 'DE',
				"shipping_default" => '1'
			),

			15 => array (
				"provider_name" => 'DPD.de',
				"ts_slug" => 'dpd-de',
				"provider_url" => 'https://tracking.dpd.de/parcelstatus?query=%number%&locale=en_DE',
				"shipping_country" => 'DE',
				"shipping_default" => '1'
			),

			16 => array (
				"provider_name" => 'DPD.ie',
				"ts_slug" => 'dpd-ie',
				"provider_url" => 'http://www2.dpd.ie/Services/QuickTrack/tabid/222/ConsignmentID/%number%/Default.aspx',
				"shipping_country" => 'IE',
				"shipping_default" => '1'
			),

			17 => array (
				"provider_name" => 'DHL Express',
				"ts_slug" => 'dhl-express',
				"provider_url" => 'http://www.dhl.it/it/express/ricerca.html?AWB=%number%&brand=DHL',
				"shipping_country" => 'Global',
				"shipping_default" => '1'
			),

			18 => array (
				"provider_name" => 'PostNL',
				"ts_slug" => 'postnl',
				"provider_url" => 'https://mijnpakket.postnl.nl/Claim?Barcode=%number%&Postalcode=%2$s&Foreign=False&ShowAnonymousLayover=False&CustomerServiceClaim=False',
				"shipping_country" => 'NL',
				"shipping_default" => '1'
			),

			19 => array (
				"provider_name" => 'DPD.NL',
				"ts_slug" => 'dpd-nl',
				"provider_url" => 'http://track.dpdnl.nl/?parcelnumber=%number%',
				"shipping_country" => 'NL',
				"shipping_default" => '1'
			),

			20 => array (
				"provider_name" => 'Fastway NZ',
				"ts_slug" => 'fastway-nz',
				"provider_url" => 'https://www.fastway.co.nz/tools/track?l=%number%',
				"shipping_country" => 'NZ',
				"shipping_default" => '1'
			),

			21 => array (
				"provider_name" => 'DPD Romania',
				"ts_slug" => 'dpd-romania',
				"provider_url" => 'https://tracking.dpd.de/parcelstatus?query=%number%&locale=ro_RO',
				"shipping_country" => 'RO',
				"shipping_default" => '1'
			),

			22 => array (
				"provider_name" => 'PostNord Sverige AB',
				"ts_slug" => 'postnord-sverige-ab',
				"provider_url" => 'http://www.postnord.se/sv/verktyg/sok/Sidor/spara-brev-paket-och-pall.aspx?search=%number%',
				"shipping_country" => 'SE',
				"shipping_default" => '1'
			),

			23 => array (
				"provider_name" => 'DHL se',
				"ts_slug" => 'dhl-se',
				"provider_url" => 'http://www.dhl.se/content/se/sv/express/godssoekning.shtml?brand=DHL&AWB=%number%',
				"shipping_country" => 'SE',
				"shipping_default" => '1'
			),

			24 => array (
				"provider_name" => 'UPS.se',
				"ts_slug" => 'ups-se',
				"provider_url" => 'http://wwwapps.ups.com/WebTracking/track?track=yes&loc=sv_SE&trackNums=%number%',
				"shipping_country" => 'SE',
				"shipping_default" => '1'
			),

			25 => array (
				"provider_name" => 'DHL uk',
				"ts_slug" => 'dhl-uk',
				"provider_url" => 'http://www.dhl.com/content/g0/en/express/tracking.shtml?brand=DHL&AWB=%number%',
				"shipping_country" => 'GB',
				"shipping_default" => '1'
			),

			26 => array (
				"provider_name" => 'DPD.co.uk',
				"ts_slug" => 'dpd-co-uk',
				"provider_url" => 'http://www.dpd.co.uk/tracking/trackingSearch.do?search.searchType=0&search.parcelNumber=%number%',
				"shipping_country" => 'GB',
				"shipping_default" => '1'
			),

			27 => array (
				"provider_name" => 'InterLink',
				"ts_slug" => 'interlink',
				"provider_url" => 'http://www.interlinkexpress.com/apps/tracking/?reference=%number%&postcode=%2$s#results',
				"shipping_country" => 'GB',
				"shipping_default" => '1'
			),

			28 => array (
				"provider_name" => 'ParcelForce',
				"ts_slug" => 'parcelforce',
				"provider_url" => 'http://www.parcelforce.com/portal/pw/track?trackNumber=%number%',
				"shipping_country" => 'GB',
				"shipping_default" => '1'
			),

			29 => array (
				"provider_name" => 'Royal Mail',
				"ts_slug" => 'royal-mail',
				"provider_url" => 'https://www.royalmail.com/track-your-item/?trackNumber=%number%',
				"shipping_country" => 'GB',
				"shipping_default" => '1'
			),

			30 => array (
				"provider_name" => 'Fedex',
				"ts_slug" => 'fedex',
				"provider_url" => 'http://www.fedex.com/Tracking?action=track&tracknumbers=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),

			31 => array (
				"provider_name" => 'FedEx Sameday',
				"ts_slug" => 'fedex-sameday',
				"provider_url" => 'https://www.fedexsameday.com/fdx_dotracking_ua.aspx?tracknum=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),

			32 => array (
				"provider_name" => 'OnTrac',
				"ts_slug" => 'ontrac',
				"provider_url" => 'http://www.ontrac.com/trackingdetail.asp?tracking=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),

			33 => array (
				"provider_name" => 'UPS',
				"ts_slug" => 'ups',
				"provider_url" => 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),

			34 => array (
				"provider_name" => 'USPS',
				"ts_slug" => 'usps',
				"provider_url" => 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),

			35 => array (
				"provider_name" => 'DHL US',
				"ts_slug" => 'dhl-us',
				"provider_url" => 'https://www.logistics.dhl/us-en/home/tracking/tracking-ecommerce.html?tracking-id=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),

			36 => array (
				"provider_name" => 'LaserShip',
				"ts_slug" => 'lasership',
				"provider_url" => 'https://www.lasership.com/track.php?track_number_input=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),
			37 => array (
				"provider_name" => 'GSO',
				"ts_slug" => 'gso',
				"provider_url" => 'https://www.gso.com/tracking',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),
			38 => array (
				"provider_name" => 'ABF',
				"ts_slug" => 'abf',
				"provider_url" => 'https://arcb.com/tools/tracking.html',
				"shipping_country" => 'IN',
				"shipping_default" => '1'
			),
			39 => array (
				"provider_name" => 'Associated Global Systems',
				"ts_slug" => 'associated-global-systems',
				"provider_url" => 'https://tracking.agsystems.com/',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),
			40 => array (
				"provider_name" => 'APC',
				"ts_slug" => 'apc',
				"provider_url" => 'https://us.mytracking.net/APC/track/TrackDetails.aspx?t=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),
			41 => array (
				"provider_name" => 'ArrowXL',
				"ts_slug" => 'arrowxl',
				"provider_url" => 'https://askaxl.co.uk/tracking?upi=%number%',
				"shipping_country" => 'GB',
				"shipping_default" => '1'
			),
			42 => array (
				"provider_name" => 'Dai Post',
				"ts_slug" => 'dai-post',
				"provider_url" => 'https://daiglobaltrack.com/tracking.aspx?custtracknbr=%number%',
				"shipping_country" => 'AU',
				"shipping_default" => '1'
			),
			43 => array (
				"provider_name" => 'Deliv',
				"ts_slug" => 'deliv',
				"provider_url" => 'https://tracking.deliv.co/',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),
			44 => array (
				"provider_name" => 'India Post',
				"ts_slug" => 'india-post',
				"provider_url" => 'https://www.indiapost.gov.in/_layouts/15/dop.portal.tracking/trackconsignment.aspx',
				"shipping_country" => 'IN',
				"shipping_default" => '1'
			),
			45 => array (
				"provider_name" => 'Israel Post',
				"ts_slug" => 'israel-post',
				"provider_url" => 'https://mypost.israelpost.co.il/itemtrace?itemcode=%number%',
				"shipping_country" => 'IL',
				"shipping_default" => '1'
			),
			46 => array (
				"provider_name" => 'Boxberry',
				"ts_slug" => 'boxberry',
				"provider_url" => 'https://boxberry.ru/tracking/',
				"shipping_country" => 'RU',
				"shipping_default" => '1'
			),
			47 => array (
				"provider_name" => 'Canpar',
				"ts_slug" => 'canpar',
				"provider_url" => 'https://www.canpar.ca/en/track/tracking.jsp',
				"shipping_country" => 'CA',
				"shipping_default" => '1'
			),
			48 => array (
				"provider_name" => 'China Post',
				"ts_slug" => 'china-post',
				"provider_url" => 'http://parcelsapp.com/en/tracking/%number%',
				"shipping_country" => 'CN',
				"shipping_default" => '1'
			),
			49 => array (
				"provider_name" => 'Chronopost',
				"ts_slug" => 'chronopost',
				"provider_url" => 'https://www.chronopost.fr/fr/chrono_suivi_search?listeNumerosLT=%number%',
				"shipping_country" => 'FR',
				"shipping_default" => '1'
			),
			50 => array (
				"provider_name" => 'Colis Privé',
				"ts_slug" => 'colis-prive',
				"provider_url" => 'https://www.colisprive.fr/',
				"shipping_country" => 'FR',
				"shipping_default" => '1'
			),
			51 => array (
				"provider_name" => 'Correos Chile',
				"ts_slug" => 'correos-chile',
				"provider_url" => 'https://seguimientoenvio.correos.cl/home/index/%number%',
				"shipping_country" => 'CL',
				"shipping_default" => '1'
			),
			52 => array (
				"provider_name" => 'Correos Costa Rica',
				"ts_slug" => 'correos-costa-rica',
				"provider_url" => 'https://www.correos.go.cr/rastreo/consulta_envios/rastreo.aspx',
				"shipping_country" => 'CR',
				"shipping_default" => '1'
			),
			53 => array (
				"provider_name" => 'CouriersPlease',
				"ts_slug" => 'couriersplease',
				"provider_url" => 'https://www.couriersplease.com.au/tools-track/no/%number%',
				"shipping_country" => 'AU',
				"shipping_default" => '1'
			),
			54 => array (
				"provider_name" => 'Delhivery',
				"ts_slug" => 'delhivery',
				"provider_url" => 'https://www.delhivery.com/track/package/%number%',
				"shipping_country" => 'IN',
				"shipping_default" => '1'
			),
			55 => array (
				"provider_name" => 'Deutsche Post',
				"ts_slug" => 'deutsche-post',
				"provider_url" => 'https://www.deutschepost.de/sendung/simpleQuery.html',
				"shipping_country" => 'DE',
				"shipping_default" => '1'
			),
			56 => array (
				"provider_name" => 'Direct Link',
				"ts_slug" => 'direct-link',
				"provider_url" => 'https://tracking.directlink.com/?itemNumber=%number%',
				"shipping_country" => 'DE',
				"shipping_default" => '1'
			),
			57 => array (
				"provider_name" => 'EC Firstclass',
				"ts_slug" => 'ec-firstclass',
				"provider_url" => 'http://www.ec-firstclass.org/Details.aspx',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),
			58 => array (
				"provider_name" => 'Ecom Express',
				"ts_slug" => 'ecom-express',
				"provider_url" => 'https://ecomexpress.in/tracking/?tflag=0&awb_field=%number%',
				"shipping_country" => 'IN',
				"shipping_default" => '1'
			),
			59 => array (
				"provider_name" => 'EMS',
				"ts_slug" => 'ems',
				"provider_url" => 'https://www.ems.post/en/global-network/tracking',
				"shipping_country" => 'CN',
				"shipping_default" => '1'
			),
			60 => array (
				"provider_name" => 'Hong Kong Post',
				"ts_slug" => 'hong-kong-post',
				"provider_url" => 'https://www.hongkongpost.hk/en/mail_tracking/index.html',
				"shipping_country" => 'HK',
				"shipping_default" => '1'
			),
			61 => array (
				"provider_name" => 'JP Post',
				"ts_slug" => 'jp-post',
				"provider_url" => 'https://trackings.post.japanpost.jp/services/srv/sequenceNoSearch/?requestNo=%number%&count=100&sequenceNoSearch.x=94&sequenceNoSearch.y=10&locale=en',
				"shipping_country" => 'JP',
				"shipping_default" => '1'
			),	
			62 => array (
				"provider_name" => 'La Poste',
				"ts_slug" => 'la-poste',
				"provider_url" => 'https://www.laposte.fr/particulier/outils/en/track-a-parcel',
				"shipping_country" => 'FR',
				"shipping_default" => '1'
			),
			63 => array (
				"provider_name" => 'Latvijas Pasts',
				"ts_slug" => 'latvijas-pasts',
				"provider_url" => 'https://www.pasts.lv/en/Category/Tracking_of_Postal_Items/',
				"shipping_country" => 'LV',
				"shipping_default" => '1'
			),
			64 => array (
				"provider_name" => 'Ninja Van',
				"ts_slug" => 'ninja-van',
				"provider_url" => 'https://www.ninjavan.co/en-sg/?tracking_id=%number%',
				"shipping_country" => 'SG',
				"shipping_default" => '1'
			),
			65 => array (
				"provider_name" => 'Singapore Post',
				"ts_slug" => 'singapore-post',
				"provider_url" => 'https://www.singpost.com/track-items',
				"shipping_country" => 'SG',
				"shipping_default" => '1'
			),
			66 => array (
				"provider_name" => 'StarTrack',
				"ts_slug" => 'startrack',
				"provider_url" => 'https://sttrackandtrace.startrack.com.au/%number%',
				"shipping_country" => 'AU',
				"shipping_default" => '1'
			),
			67 => array (
				"provider_name" => 'Ukrposhta',
				"ts_slug" => 'ukrposhta',
				"provider_url" => 'http://ukrposhta.ua/en/vidslidkuvati-forma-poshuku',
				"shipping_country" => 'UA',
				"shipping_default" => '1'
			),
			68 => array (
				"provider_name" => 'UPS i-parcel',
				"ts_slug" => 'ups-i-parcel',
				"provider_url" => 'https://tracking.i-parcel.com/?TrackingNumber=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),	
			69 => array (
				"provider_name" => 'DTDC',
				"ts_slug" => 'dtdc',
				"provider_url" => 'http://www.dtdc.in/tracking/tracking_results.asp?Ttype=awb_no&strCnno=%number%&TrkType2=awb_no',
				"shipping_country" => 'IN',
				"shipping_default" => '1'
			),	
			70 => array (
				"provider_name" => 'DHL Parcel',
				"ts_slug" => 'dhl-parcel',
				"provider_url" => 'https://www.logistics.dhl/us-en/home/tracking/tracking-ecommerce.html?tracking-id=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),
			71 => array (
				"provider_name" => 'An Post',
				"ts_slug" => 'an-post',
				"provider_url" => 'https://www.anpost.com/Post-Parcels/Track/History?item=%number%',
				"shipping_country" => 'IE',
				"shipping_default" => '1'
			),	
			72 => array (
				"provider_name" => 'Mondial Relay',
				"ts_slug" => 'mondial-relay',
				"provider_url" => 'https://www.mondialrelay.fr/suivi-de-colis?numeroExpedition=%number%',
				"shipping_country" => 'FR',
				"shipping_default" => '1'
			),
			73 => array (
				"provider_name" => 'Swiss Post',
				"ts_slug" => 'swiss-post',
				"provider_url" => 'https://service.post.ch/EasyTrack/submitParcelData.do?p_language=en&formattedParcelCodes=%number%',
				"shipping_country" => 'CH',
				"shipping_default" => '1'
			),
			74 => array (
				"provider_name" => 'S.F Express',
				"ts_slug" => 's-f-express',
				"provider_url" => 'http://www.sf-express.com/cn/en/dynamic_function/waybill/#search/bill-number/%number%',
				"shipping_country" => 'CN',
				"shipping_default" => '1'
			),
			75 => array (
				"provider_name" => 'ePacket',
				"ts_slug" => 'epacket',
				"provider_url" => 'http://www.ems.com.cn/english.html',
				"shipping_country" => 'CN',
				"shipping_default" => '1'
			),
			76 => array (
				"provider_name" => 'DTDC Plus',
				"ts_slug" => 'dtdc-plus',
				"provider_url" => 'http://www.dtdc.in/tracking/tracking_results.asp?Ttype=awb_no&strCnno=&TrkType2=awb_no',
				"shipping_country" => 'IN',
				"shipping_default" => '1'
			),	
			77 => array (
				"provider_name" => 'DHLParcel NL',
				"ts_slug" => 'dhlparcel-nl',
				"provider_url" => 'https://www.logistics.dhl/nl-en/home/tracking/tracking-parcel.html?tracking-id=%number%',
				"shipping_country" => 'NL',
				"shipping_default" => '1'
			),
			78 => array (
				"provider_name" => 'TNT',
				"ts_slug" => 'tnt',
				"provider_url" => 'https://www.tnt.com/?searchType=con&cons=%number%',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),
			79 => array (
				"provider_name" => 'Australia EMS',
				"ts_slug" => 'australia-ems',
				"provider_url" => 'https://auspost.com.au/mypost/track/#/details/%number%',
				"shipping_country" => 'AU',
				"shipping_default" => '1'
			),
			80 => array (
				"provider_name" => 'Bangladesh EMS',
				"ts_slug" => 'bangladesh-ems',
				"provider_url" => 'http://www.bangladeshpost.gov.bd/tracking.html',
				"shipping_country" => 'BD',
				"shipping_default" => '1'
			),
			81 => array (
				"provider_name" => 'Colombia Post',
				"ts_slug" => 'colombia-post',
				"provider_url" => 'http://www.4-72.com.co/',
				"shipping_country" => 'CO',
				"shipping_default" => '1'
			),
			82 => array (
				"provider_name" => 'Costa Rica Post',
				"ts_slug" => 'costa-rica-post',
				"provider_url" => 'https://www.correos.go.cr/rastreo/consulta_envios/',
				"shipping_country" => 'CR',
				"shipping_default" => '1'
			),
			83 => array (
				"provider_name" => 'Croatia Post',
				"ts_slug" => 'croatia-post',
				"provider_url" => 'https://www.posta.hr/tracktrace.aspx?broj=%number%',
				"shipping_country" => 'HR',
				"shipping_default" => '1'
			),
			84 => array (
				"provider_name" => 'Cyprus Post',
				"ts_slug" => 'cyprus-post',
				"provider_url" => 'https://www.cypruspost.post/en/track-n-trace-results?code=%number%',
				"shipping_country" => 'CY',
				"shipping_default" => '1'
			),
			85 => array (
				"provider_name" => 'Denmark Post',
				"ts_slug" => 'denmark-post',
				"provider_url" => 'https://www.postnord.dk/en/track-and-trace#dynamicloading=true&shipmentid=%number%',
				"shipping_country" => 'DK',
				"shipping_default" => '1'
			),
			86 => array (
				"provider_name" => 'Estonia Post',
				"ts_slug" => 'estonia-post',
				"provider_url" => 'https://www.omniva.ee/private/track_and_trace',
				"shipping_country" => 'EE',
				"shipping_default" => '1'
			),
			87 => array (
				"provider_name" => 'France EMS - Chronopost',
				"ts_slug" => 'france-ems-chronopost',
				"provider_url" => 'https://www.chronopost.fr/tracking-no-cms/suivi-page?listeNumerosLT=%number%',
				"shipping_country" => 'FR',
				"shipping_default" => '1'
			),
			88 => array (
				"provider_name" => 'Ivory Coast EMS',
				"ts_slug" => 'ivory-coast-ems',
				"provider_url" => 'https://laposte.ci.post/tracking-colis?identifiant=%number%',
				"shipping_country" => 'CI',
				"shipping_default" => '1'
			),
			89 => array (
				"provider_name" => 'Korea Post',
				"ts_slug" => 'korea-post',
				"provider_url" => 'https://service.epost.go.kr/trace.RetrieveEmsRigiTraceList.comm?ems_gubun=E&sid1=&POST_CODE=%number%',
				"shipping_country" => 'KR',
				"shipping_default" => '1'
			),
			90 => array (
				"provider_name" => 'Monaco EMS',
				"ts_slug" => 'monaco-ems',
				"provider_url" => 'http://www.lapostemonaco.mc',
				"shipping_country" => 'MC',
				"shipping_default" => '1'
			),				
			91 => array (
				"provider_name" => 'Overseas Territory FR EMS',
				"ts_slug" => 'overseas-territory-fr-ems',
				"provider_url" => 'https://www.chronopost.fr/tracking-no-cms/suivi-page?listeNumerosLT=%number%',
				"shipping_country" => 'FR',
				"shipping_default" => '1'
			),
			92 => array (
				"provider_name" => 'Portugal Post - CTT',
				"ts_slug" => 'portugal-post-ctt',
				"provider_url" => 'http://www.ctt.pt/feapl_2/app/open/objectSearch/objectSearch.jspx',
				"shipping_country" => 'PT',
				"shipping_default" => '1'
			),
			93 => array (
				"provider_name" => 'South African Post Office',
				"ts_slug" => 'south-african-post-office',
				"provider_url" => 'http://www.southafricanpostoffice.post/index.html',
				"shipping_country" => 'ZA',
				"shipping_default" => '1'
			),	
			94 => array (
				"provider_name" => 'Ukraine EMS',
				"ts_slug" => 'ukraine-ems',
				"provider_url" => 'http://dpsz.ua/en/track/ems',
				"shipping_country" => 'UA',
				"shipping_default" => '1'
			),
			95 => array (
				"provider_name" => 'TNT Italy',
				"ts_slug" => 'tnt-italy',
				"provider_url" => 'https://www.tnt.it/tracking/Tracking.do',
				"shipping_country" => 'IT',
				"shipping_default" => '1'
			),
			96 => array (
				"provider_name" => 'TNT France',
				"ts_slug" => 'tnt-france',
				"provider_url" => 'https://www.tnt.fr/public/suivi_colis/recherche/visubontransport.do',
				"shipping_country" => 'FR',
				"shipping_default" => '1'
			),				
			97 => array (
				"provider_name" => 'TNT UK',
				"ts_slug" => 'tnt-uk',
				"provider_url" => 'https://www.tnt.com/?searchType=con&cons=%number%',
				"shipping_country" => 'GB',
				"shipping_default" => '1'
			),
			98 => array (
				"provider_name" => 'Aliexpress Standard Shipping',
				"ts_slug" => 'aliexpress-standard-shipping',
				"provider_url" => 'https://global.cainiao.com/detail.htm?mailNoList=LP00139185155139',
				"shipping_country" => 'Global',
				"shipping_default" => '1'
			),
			99 => array (
				"provider_name" => 'Speedex Courier',
				"ts_slug" => 'speedex-courier',
				"provider_url" => 'http://www.speedexcourier.com/',
				"shipping_country" => 'US',
				"shipping_default" => '1'
			),
			100 => array (
				"provider_name" => 'TNT Reference',
				"ts_slug" => 'tnt-reference',
				"provider_url" => 'https://www.tnt.com/express/en_gb/site/shipping-tools/tracking.html?searchType=con&cons=%number%',
				"shipping_country" => 'GB',
				"shipping_default" => '1'
			),				
			101 => array (
				"provider_name" => 'TNT Click',
				"ts_slug" => 'tnt-click',
				"provider_url" => 'https://www.tnt-click.it/',
				"shipping_country" => 'IT',
				"shipping_default" => '1'
			),	
			102 => array (
				"provider_name" => 'TNT Australia',
				"ts_slug" => 'tnt-australia',
				"provider_url" => 'https://www.tnt.com/express/en_au/site/shipping-tools/tracking.html?respCountry=au&respLang=en&cons=%number%',
				"shipping_country" => 'AU',
				"shipping_default" => '1'
			),
			103 => array (
				"provider_name" => 'DHL Freight',
				"ts_slug" => 'dhl-freight',
				"provider_url" => 'https://www.logistics.dhl/global-en/home/tracking/tracking-freight.html?tracking-id=%number%',
				"shipping_country" => 'Global',
				"shipping_default" => '1'
			),
			104 => array (
				"provider_name" => 'Sendle',
				"ts_slug" => 'sendle',
				"provider_url" => 'https://track.sendle.com/tracking?ref=%number%',
				"shipping_country" => 'AU',
				"shipping_default" => '1'
			),
			105 => array (
				"provider_name" => 'Deppon',
				"ts_slug" => 'deppon',
				"provider_url" => 'https://www.deppon.com/en/toTrack.action',
				"shipping_country" => 'CN',
				"shipping_default" => '1',					
			),	
			106 => array (
				"provider_name" => 'GLS Italy',
				"ts_slug" => 'gls-italy',
				"provider_url" => 'https://www.gls-italy.com/?option=com_gls&view=track_e_trace&mode=search&numero_spedizione=%number%&tipo_codice=nazionale',
				"shipping_country" => 'IT',
				"shipping_default" => '1',					
			),
			107 => array (
				"provider_name" => 'Hermes World',
				"ts_slug" => 'hermes',
				"provider_url" => 'https://new.myhermes.co.uk/track.html#/parcel/%number%/details',
				"shipping_country" => 'Global',
				"shipping_default" => '1',					
			),				
		);		
		return $providers;
	}	
	
	/*
	* database update
	*/
	public function update_database_check(){					
		if ( is_admin() ){			
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'1.2', '>=') ){				
			}
			else{				
				global $wpdb;
				$results = $wpdb->get_row( "SELECT * FROM {$this->table} LIMIT 1");				
				if(!isset($results->sort_order)) {
					$res = $wpdb->query( sprintf( "ALTER TABLE %s ADD sort_order int(11) NOT NULL DEFAULT '0'", $this->table) );
				}				
				update_option( 'wc_advanced_shipment_tracking', '1.2');				
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'1.3', '>=') ){						
			}
			else{				
				global $wpdb;
				$results = $wpdb->get_row( "SELECT * FROM {$this->table} LIMIT 1");				
				if(!isset($results->custom_thumb_id)) {
					$res = $wpdb->query( sprintf( "ALTER TABLE %s ADD custom_thumb_id int(11) NOT NULL DEFAULT '0'", $this->table) );
				}			
				update_option( 'wc_advanced_shipment_tracking', '1.3');				
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'1.4', '>=') ){						
			}
			else{				
				global $wpdb;
				$woo_shippment_status_email_table = $wpdb->prefix . 'woo_shipment_status_email';
				// create the ECPT metabox database table
				if($wpdb->get_var("show tables like '$woo_shippment_status_email_table'") != $woo_shippment_status_email_table) {
					$charset_collate = $wpdb->get_charset_collate();
					
					$sql = "CREATE TABLE $woo_shippment_status_email_table (
						id int(11) NOT NULL AUTO_INCREMENT,
						email_trigger text NOT NULL,
						email_label text NOT NULL,
						email_to text NOT NULL,
						shippment_status text NOT NULL,
						order_status text NOT NULL,
						email_subject text NOT NULL,
						email_heading text NOT NULL,
						email_content text NOT NULL,				
						PRIMARY KEY  (id)
					) $charset_collate;";
					//echo $sql;exit;
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta( $sql );
				}					
				update_option( 'wc_advanced_shipment_tracking', '1.4');		
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'1.5', '>=') ){						
			}
			else{
				global $wpdb;
				$woo_shippment_status_email_table = $wpdb->prefix . 'woo_shipment_status_email';
				$results = $wpdb->get_row( "SELECT * FROM {$woo_shippment_status_email_table} LIMIT 1");
				
				if(!isset($results->enable)) {
					$res = $wpdb->query( sprintf( "ALTER TABLE %s ADD enable int(11) NOT NULL DEFAULT '1'", $woo_shippment_status_email_table) );						
				}
				update_option( 'wc_advanced_shipment_tracking', '1.5');	
			}			
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'2.4', '<') ){			
				global $wpdb;
				$woo_shippment_table_name = $wpdb->prefix . 'woo_shippment_provider';
				$providers = $this->provider_list();
				
				/***** Remove some default shipping providers *****/	
				$provider_array = array('PPL.cz','Česká pošta','Itella','Courier Post','NZ Post','PBT Couriers','Fan Courier','Urgent Cargus','SAPO','Bring.se','DB Schenker','UK Mail','Royal Mail','TNT Express (reference)','DHL Parcel','DHL Logistics','StarTrack','Chronopost','ArrowXL');				
				
				foreach($provider_array as $provider){					
					$return = $wpdb->delete( $woo_shippment_table_name, array('provider_name'=>$provider,'shipping_default' => 1));
				}
				
				/**** If provider is not availalble than add and if more than one than remove extra *****/
				foreach($providers as $provider){
					$rows = $wpdb->get_results( "SELECT * FROM $woo_shippment_table_name WHERE provider_name='".$provider['provider_name']."'" );
					$count = count($rows);
					if($count == 0){
						$success = $wpdb->insert($woo_shippment_table_name, array(
							"provider_name" => $provider['provider_name'],
							"provider_url" => $provider['provider_url'],
							"shipping_country" => $provider['shipping_country'],
							"shipping_default" => $provider['shipping_default'],
						));
					}
					if($count > 1){	
						$n = 0;
						foreach($rows as $row){
							if($n > 0){
								$return = $wpdb->delete( $woo_shippment_table_name, array('id'=>$row->id));
							}							
							$n++;
						}
					}				
				}				
				update_option( 'wc_advanced_shipment_tracking', '2.4');								
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'2.5', '<') ){				
				global $wpdb;
				$woo_shippment_table_name = $wpdb->prefix . 'woo_shippment_provider';
				
				$data_array = array(
					'provider_url' => 'https://www.delhivery.com/track/package/%number%',
				);
				$where_array = array(
					'provider_name' => 'Delhivery'
				);				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				update_option( 'wc_advanced_shipment_tracking', '2.5');
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'2.6', '<') ){				
				global $wpdb;
				$woo_shippment_table_name = $wpdb->prefix . 'woo_shippment_provider';
				
				$data_array = array(
					'provider_url' => 'https://www.couriersplease.com.au/tools-track/no/%number%',
				);
				$where_array = array(
					'provider_name' => 'CouriersPlease'
				);				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );						
				
				$data_array = array(
					'provider_url' => 'https://mypost.israelpost.co.il/itemtrace?itemcode=%number%',
				);
				$where_array = array(
					'provider_name' => 'Israel Post'
				);				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				update_option( 'wc_advanced_shipment_tracking', '2.6');
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'2.7', '<') ){
				global $wpdb;
				$woo_shippment_table_name = $wpdb->prefix . 'woo_shippment_provider';				 
				$provider = array (
					"provider_name" => 'Mondial Relay',
					"provider_url" => 'https://www.mondialrelay.fr/suivi-de-colis?codeMarque=CC&numeroExpedition=%number%',
					"shipping_country" => 'FR',
					"shipping_default" => '1'
				);				
				$success = $wpdb->insert($woo_shippment_table_name, array(
					"provider_name" => $provider['provider_name'],
					"provider_url" => $provider['provider_url'],
					"shipping_country" => $provider['shipping_country'],
					"shipping_default" => $provider['shipping_default'],
				));
				update_option( 'wc_advanced_shipment_tracking', '2.7');	
			}	
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'2.8', '<') ){
				global $wpdb;
				$woo_shippment_table_name = $wpdb->prefix . 'woo_shippment_provider';				 				
				
				$data_array = array( 'provider_url' => 'https://www.mondialrelay.fr/suivi-de-colis?numeroExpedition=%number%');
				$where_array = array( 'provider_name' => 'Mondial Relay' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				$data_array = array( 'provider_name' => 'Brazil Correios');
				$where_array = array( 'provider_name' => 'Correios' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				$data_array = array( 'provider_name' => 'DHL at');
				$where_array = array( 'provider_name' => 'DHL.at' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				$data_array = array( 'provider_name' => 'DHL cz');
				$where_array = array( 'provider_name' => 'DHL.cz' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				$data_array = array( 'provider_name' => 'DHL se');
				$where_array = array( 'provider_name' => 'DHL.se' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );

				$data_array = array( 'provider_name' => 'DHL uk');
				$where_array = array( 'provider_name' => 'DHL' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				$data_array = array( 'provider_name' => 'Belgium Post');
				$where_array = array( 'provider_name' => 'bpost' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				$data_array = array( 'shipping_country' => 'Global');
				$where_array = array( 'provider_name' => 'DHL Express' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				$data_array = array( 'shipping_country' => 'Global');
				$where_array = array( 'provider_name' => 'Aliexpress Standard Shipping' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				$data_array = array( 'provider_url' => 'https://www.laposte.fr/outils/suivre-vos-envois?code=%number%');
				$where_array = array( 'provider_name' => 'La Poste' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				$data_array = array( 'provider_url' => 'https://www.laposte.fr/outils/suivre-vos-envois?code=%number%');
				$where_array = array( 'provider_name' => 'Colissimo' );				
				$result = $wpdb->update( $woo_shippment_table_name, $data_array, $where_array );
				
				$providers = array (
					1 => array (
						"provider_name" => 'Swiss Post',
						"provider_url" => 'https://service.post.ch/EasyTrack/submitParcelData.do?p_language=en&formattedParcelCodes=%number%',
						"shipping_country" => 'CH',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					2 => array (
						"provider_name" => 'S.F Express',
						"provider_url" => 'http://www.sf-express.com/cn/en/dynamic_function/waybill/#search/bill-number/%number%',
						"shipping_country" => 'CN',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					3 => array (
						"provider_name" => 'ePacket',
						"provider_url" => 'http://www.ems.com.cn/english.html',
						"shipping_country" => 'CN',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					4 => array (
						"provider_name" => 'DTDC Plus',
						"provider_url" => 'http://www.dtdc.in/tracking/tracking_results.asp?Ttype=awb_no&strCnno=&TrkType2=awb_no',
						"shipping_country" => 'IN',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					5 => array (
						"provider_name" => 'DHLParcel NL',
						"provider_url" => 'https://www.logistics.dhl/nl-en/home/tracking/tracking-parcel.html?tracking-id=%number%',
						"shipping_country" => 'NL',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					6 => array (
						"provider_name" => 'TNT',
						"provider_url" => 'https://www.tnt.com/?searchType=con&cons=%number%',
						"shipping_country" => 'US',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					7 => array (
						"provider_name" => 'Australia EMS',
						"provider_url" => 'https://auspost.com.au/mypost/track/#/details/%number%',
						"shipping_country" => 'AU',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					8 => array (
						"provider_name" => 'Bangladesh EMS',
						"provider_url" => 'http://www.bangladeshpost.gov.bd/tracking.html',
						"shipping_country" => 'BD',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					9 => array (
						"provider_name" => 'Colombia Post',
						"provider_url" => 'http://www.4-72.com.co/',
						"shipping_country" => 'CO',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					10 => array (
						"provider_name" => 'Costa Rica Post',
						"provider_url" => 'https://www.correos.go.cr/rastreo/consulta_envios/',
						"shipping_country" => 'CR',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					11 => array (
						"provider_name" => 'Croatia Post',
						"provider_url" => 'https://www.posta.hr/tracktrace.aspx?broj=%number%',
						"shipping_country" => 'HR',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					12 => array (
						"provider_name" => 'Cyprus Post',
						"provider_url" => 'https://www.cypruspost.post/en/track-n-trace-results?code=%number%',
						"shipping_country" => 'CY',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					13 => array (
						"provider_name" => 'Denmark Post',
						"provider_url" => 'https://www.postnord.dk/en/track-and-trace#dynamicloading=true&shipmentid=%number%',
						"shipping_country" => 'DK',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					14 => array (
						"provider_name" => 'Estonia Post',
						"provider_url" => 'https://www.omniva.ee/private/track_and_trace',
						"shipping_country" => 'EE',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					15 => array (
						"provider_name" => 'France EMS - Chronopost',
						"provider_url" => 'https://www.chronopost.fr/tracking-no-cms/suivi-page?listeNumerosLT=%number%',
						"shipping_country" => 'FR',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					16 => array (
						"provider_name" => 'Ivory Coast EMS',
						"provider_url" => 'https://laposte.ci.post/tracking-colis?identifiant=%number%',
						"shipping_country" => 'CI',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					17 => array (
						"provider_name" => 'Korea Post',
						"provider_url" => 'https://service.epost.go.kr/trace.RetrieveEmsRigiTraceList.comm?ems_gubun=E&sid1=&POST_CODE=%number%',
						"shipping_country" => 'KR',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					18 => array (
						"provider_name" => 'Monaco EMS',
						"provider_url" => 'http://www.lapostemonaco.mc',
						"shipping_country" => 'MC',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),				
					19 => array (
						"provider_name" => 'Overseas Territory FR EMS',
						"provider_url" => 'https://www.chronopost.fr/tracking-no-cms/suivi-page?listeNumerosLT=%number%',
						"shipping_country" => 'FR',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					20 => array (
						"provider_name" => 'Portugal Post - CTT',
						"provider_url" => 'http://www.ctt.pt/feapl_2/app/open/objectSearch/objectSearch.jspx',
						"shipping_country" => 'PT',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					21 => array (
						"provider_name" => 'South African Post Office',
						"provider_url" => 'http://www.southafricanpostoffice.post/index.html',
						"shipping_country" => 'ZA',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),	
					22 => array (
						"provider_name" => 'Ukraine EMS',
						"provider_url" => 'http://dpsz.ua/en/track/ems',
						"shipping_country" => 'UA',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					23 => array (
						"provider_name" => 'TNT Italy',
						"provider_url" => 'https://www.tnt.it/tracking/Tracking.do',
						"shipping_country" => 'IT',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					24 => array (
						"provider_name" => 'TNT France',
						"provider_url" => 'https://www.tnt.fr/public/suivi_colis/recherche/visubontransport.do',
						"shipping_country" => 'FR',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),				
					25 => array (
						"provider_name" => 'TNT UK',
						"provider_url" => 'https://www.tnt.com/?searchType=con&cons=%number%',
						"shipping_country" => 'GB',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					26 => array (
						"provider_name" => 'Aliexpress Standard Shipping',
						"provider_url" => 'https://global.cainiao.com/detail.htm?mailNoList=LP00139185155139',
						"shipping_country" => 'Global',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					27 => array (
						"provider_name" => 'Speedex Courier',
						"provider_url" => 'http://www.speedexcourier.com/',
						"shipping_country" => 'US',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					28 => array (
						"provider_name" => 'TNT Reference',
						"provider_url" => 'https://www.tnt.com/express/en_gb/site/shipping-tools/tracking.html?searchType=con&cons=%number%',
						"shipping_country" => 'GB',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),				
					29 => array (
						"provider_name" => 'TNT Click',
						"provider_url" => 'https://www.tnt-click.it/',
						"shipping_country" => 'IT',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),	
					30 => array (
						"provider_name" => 'TNT Australia',
						"provider_url" => 'https://www.tnt.com/express/en_au/site/shipping-tools/tracking.html?respCountry=au&respLang=en&cons=%number%',
						"shipping_country" => 'AU',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),					
				);
				
				foreach($providers as $provider){	
					$success = $wpdb->insert($woo_shippment_table_name, array(
						"provider_name" => $provider['provider_name'],
						"provider_url" => $provider['provider_url'],
						"shipping_country" => $provider['shipping_country'],
						"shipping_default" => $provider['shipping_default'],
						"display_in_order" => $provider['display_in_order'],
					));				
				}
				update_option( 'wc_advanced_shipment_tracking', '2.8');	
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'2.9', '<') ){
				global $wpdb;
				$results = $wpdb->get_row( "SELECT * FROM {$this->table} LIMIT 1");				
				if(!isset($results->ts_slug)) {
					$res = $wpdb->query( sprintf( "ALTER TABLE %s ADD ts_slug text NULL DEFAULT NULL AFTER provider_name", $this->table) );
				}
				
				$providers = $this->provider_list();
				foreach($providers as $provider){
					
					$data_array = array(
						'ts_slug' => sanitize_title($provider['provider_name']),
					);
					$where_array = array(
						'provider_name' => $provider['provider_name']
					);				
					$result = $wpdb->update( $this->table, $data_array, $where_array );
						
				}	
				
				$new_providers = array (
					1 => array (
						"provider_name" => 'DHL Freight',
						"ts_slug" => 'dhl-freight',
						"provider_url" => 'https://www.logistics.dhl/global-en/home/tracking/tracking-freight.html?tracking-id=%number%',
						"shipping_country" => 'Global',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					2 => array (
						"provider_name" => 'Sendle',
						"ts_slug" => 'sendle',
						"provider_url" => 'https://track.sendle.com/tracking?ref=%number%',
						"shipping_country" => 'AU',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),
					3 => array (
						"provider_name" => 'Deppon',
						"ts_slug" => 'deppon',
						"provider_url" => 'https://www.deppon.com/en/toTrack.action',
						"shipping_country" => 'CN',
						"shipping_default" => '1',
						"display_in_order" => 0,
					),				
				);
				
				foreach($new_providers as $provider){	
					$success = $wpdb->insert($this->table, array(
						"provider_name" => $provider['provider_name'],
						"ts_slug" => $provider['ts_slug'],
						"provider_url" => $provider['provider_url'],
						"shipping_country" => $provider['shipping_country'],
						"shipping_default" => $provider['shipping_default'],
						"display_in_order" => $provider['display_in_order'],
					));				
				}
				
				$data_array = array( 'provider_url' => 'https://seguimientoenvio.correos.cl/home/index/%number%');
				$where_array = array( 'provider_name' => 'Correos Chile' );				
				$result = $wpdb->update( $this->table, $data_array, $where_array );
				
				$data_array = array( 'provider_name' => 'Fastway AU' , 'ts_slug' => 'fastway-au');
				$where_array = array( 'provider_name' => 'Fastway Couriers' );				
				$result = $wpdb->update( $this->table, $data_array, $where_array );
				
				$data_array = array( 'provider_name' => 'Fastway NZ', 'provider_url' => 'https://www.fastway.co.nz/tools/track?l=%number%', 'ts_slug' => 'fastway-nz');
				$where_array = array( 'provider_name' => 'Fastways' );				
				$result = $wpdb->update( $this->table, $data_array, $where_array );
				
				$custom_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE shipping_default = 0" );
				
				foreach($custom_providers as $custom){
					$data_array = array(
						'ts_slug' => sanitize_title($custom->provider_name),
					);
					$where_array = array(
						'provider_name' => $custom->provider_name
					);				
					$result = $wpdb->update( $this->table, $data_array, $where_array );
				}
				
				update_option( 'wc_advanced_shipment_tracking', '2.9');	
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.0', '<') ){
				global $wpdb;
				$new_providers = array (
					1 => array (
						"provider_name" => 'GLS Italy',
						"ts_slug" => 'gls-italy',
						"provider_url" => 'https://www.gls-italy.com/?option=com_gls&view=track_e_trace&mode=search&numero_spedizione=%number%&tipo_codice=nazionale',
						"shipping_country" => 'IT',
						"shipping_default" => '1',
						"display_in_order" => 0,
					)			
				);
				
				foreach($new_providers as $provider){	
					$success = $wpdb->insert($this->table, array(
						"provider_name" => $provider['provider_name'],
						"ts_slug" => $provider['ts_slug'],
						"provider_url" => $provider['provider_url'],
						"shipping_country" => $provider['shipping_country'],
						"shipping_default" => $provider['shipping_default'],
						"display_in_order" => $provider['display_in_order'],
					));				
				}
				update_option( 'wc_advanced_shipment_tracking', '3.0');	
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.1', '<') ){				
				$this->update_shipping_providers();
				update_option( 'wc_advanced_shipment_tracking', '3.1');	
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.2', '<') ){				
				$this->update_shipping_providers();
				update_option( 'wc_advanced_shipment_tracking', '3.2');	
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.3', '<') ){
				global $wpdb;				
				$results = $wpdb->get_row( "SELECT * FROM {$this->table} LIMIT 1");				
				if(!isset($results->ts_slug)) {					
					$res = $wpdb->query( sprintf( "ALTER TABLE %s ADD ts_slug text NULL DEFAULT NULL AFTER provider_name", $this->table) );
				}
				$this->update_shipping_providers();
				update_option( 'wc_advanced_shipment_tracking', '3.3');	
			}			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.4', '<') ){
				$multi_checkbox_data = get_option('wc_ast_unclude_tracking_info');
				$data_array = array('completed' => 1);
				if($multi_checkbox_data){	
					$data_array = array_merge($multi_checkbox_data,$data_array);
				}				
				update_option( 'wc_ast_unclude_tracking_info', $data_array );
				update_option( 'wc_advanced_shipment_tracking', '3.4');					
			}	
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.5', '<') ){
				$multi_checkbox_data = get_option('wc_ast_unclude_tracking_info');
				$data_array = array('partial-shipped' => 1);
				if($multi_checkbox_data){	
					$data_array = array_merge($multi_checkbox_data,$data_array);
				}				
				update_option( 'wc_ast_unclude_tracking_info', $data_array );
				update_option( 'wc_advanced_shipment_tracking', '3.5');					
			}				
		}
	}
	
	/**
	 * get providers list from trackship and update providers in database
	*/
	public function update_shipping_providers(){
		global $wpdb;		
		$url = 'https://trackship.info/wp-json/WCAST/v1/Provider';		
		$resp = wp_remote_get( $url );
		
		if ( is_array( $resp ) && ! is_wp_error( $resp ) ) {
		
			$providers = json_decode($resp['body'],true);
			
			$providers_name = array();
			
			$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE shipping_default = 1" );
			
			foreach ( $default_shippment_providers as $key => $val ){
				$shippment_providers[ $val->provider_name ] = $val;						
			}
	
			foreach ( $providers as $key => $val ){
				$providers_name[ $val['provider_name'] ] = $val;						
			}					
			
			foreach($providers as $provider){
				
				$provider_name = $provider['shipping_provider'];
				$provider_url = $provider['provider_url'];
				$shipping_country = $provider['shipping_country'];
				$ts_slug = $provider['shipping_provider_slug'];
				
				if(isset($shippment_providers[$provider_name])){				
					$db_provider_url = $shippment_providers[$provider_name]->provider_url;
					$db_shipping_country = $shippment_providers[$provider_name]->shipping_country;
					$db_ts_slug = $shippment_providers[$provider_name]->ts_slug;
					if(($db_provider_url != $provider_url) || ($db_shipping_country != $shipping_country) || ($db_ts_slug != $ts_slug)){
						$data_array = array(
							'ts_slug' => $ts_slug,
							'provider_url' => $provider_url,
							'shipping_country' => $shipping_country,						
						);
						$where_array = array(
							'provider_name' => $provider_name,			
						);					
						$wpdb->update( $this->table, $data_array, $where_array);					
					}
				} else{
					$img_url = $provider['img_url'];
					$img_slug = sanitize_title($provider_name);
					$img = wc_advanced_shipment_tracking()->get_plugin_path().'/assets/shipment-provider-img/'.$img_slug.'.png';
					
					$ch = curl_init(); 
	
					curl_setopt($ch, CURLOPT_HEADER, 0); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					curl_setopt($ch, CURLOPT_URL, $img_url); 
				
					$data = curl_exec($ch); 
					curl_close($ch); 
					
					file_put_contents($img, $data); 			
								
									
					$data_array = array(
						'shipping_country' => sanitize_text_field($shipping_country),
						'provider_name' => sanitize_text_field($provider_name),
						'ts_slug' => $ts_slug,
						'provider_url' => sanitize_text_field($provider_url),			
						'display_in_order' => 0,
						'shipping_default' => 1,
					);
					$result = $wpdb->insert( $this->table, $data_array );				
				}		
			}		
			foreach($default_shippment_providers as $db_provider){
	
				if(!isset($providers_name[$db_provider->provider_name])){				
					$where = array(
						'provider_name' => $db_provider->provider_name,
						'shipping_default' => 1
					);
					$wpdb->delete( $this->table, $where );					
				}
			}
		}	
	}

	/*
	* Display admin notice addons
	*/	
	public function admin_notice_for_addon(){
		if ( ! PAnD::is_admin_notice_active( 'disable-ast-addon-forever' ) ) {
			return;
		}
		?>		
		<style>		
		.notice.addon-admin-notice {			
			padding: 20px;
			background: #F6FBFF;
			border: 1px solid #eee;
			border-left: 4px solid #005B9A !important;
		}
		.rtl .notice.addon-admin-notice {
			border-right-color: #83bd31 !important;
		}
		.notice.addon-admin-notice .ast-admin-notice-inner {
			display: table;
			width: 100%;
		}
		.notice.addon-admin-notice .ast-admin-notice-inner .ast-admin-notice-icon,
		.notice.addon-admin-notice .ast-admin-notice-inner .ast-admin-notice-content,
		.notice.addon-admin-notice .ast-admin-notice-inner .trackship-install-now {
			display: table-cell;
			vertical-align: middle;
		}
		.notice.addon-admin-notice .ast-admin-notice-icon {
			color: #83bd31;			
		}
		.notice.addon-admin-notice .ast-admin-notice-icon .notice-logo{
			width: 200px;
		}
		.notice.addon-admin-notice .ast-admin-notice-content {
			padding: 0 20px;
		}
		.notice.addon-admin-notice p {
			padding: 0;
			margin: 0;
		}
		.notice.addon-admin-notice h3 {
			margin: 0 0 5px;
			color: #005B9A;
		}
		.notice.addon-admin-notice .trackship-install-now {
			text-align: center;
		}
		.notice.addon-admin-notice .trackship-install-now .hello-elementor-install-button {
			padding: 5px 30px;
			height: auto;
			line-height: 20px;
			text-transform: capitalize;
		}
		.notice.addon-admin-notice .trackship-install-now .hello-elementor-install-button i {
			padding-right: 5px;
		}
		.rtl .notice.addon-admin-notice .trackship-install-now .hello-elementor-install-button i {
			padding-right: 0;
			padding-left: 5px;
		}
		.notice.addon-admin-notice .trackship-install-now .hello-elementor-install-button:active {
			transform: translateY(1px);
		}
		.addon-admin-notice .notice-dismiss:before{
			color: #005B9A;
			font: normal 20px/20px dashicons;
		}
		.wp-core-ui .btn_blue {
			background: #005B9A;
			text-shadow: none;
			border-color: #005B9A;
			box-shadow: none;
			font-size: 14px;
			line-height: 32px;
			height: 35px;
			padding: 0 20px;
		}
		.wp-core-ui .btn_blue:hover, .wp-core-ui .btn_blue:focus {
			background: #005B9A;
			border-color: rgba(0,0,0,0.05);
			color: #fff;
			text-shadow: none;
			box-shadow: inset 0 0 0 100px rgba(0,0,0,0.2);
		}
		</style>	
		<div data-dismissible="disable-ast-addon-forever" class="notice updated is-dismissible addon-admin-notice">
			<div class="ast-admin-notice-inner">
				<div class="ast-admin-notice-icon">
					<img class="notice-logo" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url().'/assets/images/ast-addons-notice-image.jpg'; ?>" alt="Tracking Per Item Add-on" />
				</div>
		
				<div class="ast-admin-notice-content">
					<h3>New AST Add-on!</h3>
					<p>The Tracking Per Item add-on allows attaching the tracking numbers to specific order items.</p>					
				</div>
		
				<div data-dismissible="disable-ast-addon-forever" class="trackship-install-now">
					<a class="button button-primary btn_blue btn_large" href="<?php echo admin_url( '/admin.php?page=woocommerce-advanced-shipment-tracking&tab=addons' )?>">For more info</a>
				</div>
			</div>
		</div>
	<?php 
	}

	/*
	* Display admin notice on plugin install or update
	*/
	public function admin_notice_after_update(){			
		if ( ! PAnD::is_admin_notice_active( 'disable-ts-notice-new-forever' ) ) {
			return;
		}
		?>		
		<style>		
		.notice.ast-admin-notice {			
			padding: 20px;
			background: rgb(245, 248, 250);
			border: 1px solid #eee;
			border-left: 4px solid #83bd31 !important;
		}
		.rtl .notice.ast-admin-notice {
			border-right-color: #83bd31 !important;
		}
		.notice.ast-admin-notice .ast-admin-notice-inner {
			display: table;
			width: 100%;
		}
		.notice.ast-admin-notice .ast-admin-notice-inner .ast-admin-notice-icon,
		.notice.ast-admin-notice .ast-admin-notice-inner .ast-admin-notice-content,
		.notice.ast-admin-notice .ast-admin-notice-inner .trackship-install-now {
			display: table-cell;
			vertical-align: middle;
		}
		.notice.ast-admin-notice .ast-admin-notice-icon {
			color: #83bd31;			
		}
		.notice.ast-admin-notice .ast-admin-notice-icon .notice-logo{
			width: 200px;
		}
		.notice.ast-admin-notice .ast-admin-notice-content {
			padding: 0 20px;
		}
		.notice.ast-admin-notice p {
			padding: 0;
			margin: 0;
		}
		.notice.ast-admin-notice h3 {
			margin: 0 0 5px;
			color: #061c58;
		}
		.notice.ast-admin-notice .trackship-install-now {
			text-align: center;
		}
		.notice.ast-admin-notice .trackship-install-now .hello-elementor-install-button {
			padding: 5px 30px;
			height: auto;
			line-height: 20px;
			text-transform: capitalize;
		}
		.notice.ast-admin-notice .trackship-install-now .hello-elementor-install-button i {
			padding-right: 5px;
		}
		.rtl .notice.ast-admin-notice .trackship-install-now .hello-elementor-install-button i {
			padding-right: 0;
			padding-left: 5px;
		}
		.notice.ast-admin-notice .trackship-install-now .hello-elementor-install-button:active {
			transform: translateY(1px);
		}
		.ast-admin-notice .notice-dismiss:before{
			color: #061c58;
			font: normal 20px/20px dashicons;
		}
		.wp-core-ui .btn_green2 {
			background: #83bd31;
			text-shadow: none;
			border-color: #83bd31;
			box-shadow: none;
			font-size: 14px;
			line-height: 32px;
			height: 35px;
			padding: 0 20px;
		}
		.wp-core-ui .btn_green2:hover, .wp-core-ui .btn_green2:focus {
			background: rgba(131, 189, 49, 0.8);
			border-color: rgba(131, 189, 49, 0.8);
			color: #fff;
			text-shadow: none;
			box-shadow: inset 0 0 0 100px rgba(0,0,0,0.2);
		}
		</style>	
		<div data-dismissible="disable-ts-notice-new-forever" class="notice updated is-dismissible ast-admin-notice">
			<div class="ast-admin-notice-inner">
				<div class="ast-admin-notice-icon">
					<img class="notice-logo" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url().'/assets/images/trackship-400.png'; ?>" alt="Trackship Logo" />
				</div>
		
				<div class="ast-admin-notice-content">
					<h3>Thanks for using the Advanced Shipment Tracking!</h3>
					<p>
						<p>Add TrackShip integration and automate your post shipping operations!</p>
						<a href="https://trackship.info/" target="_blank">Try trackship with 50 free trackers >></a>
					</p>
				</div>
		
				<div class="trackship-install-now">
					<a class="button button-primary btn_green2 btn_large" href="https://trackship.info/" target="_blank">SIGNUP NOW</a>
				</div>
			</div>
		</div>
	<?php 		
	}	
	/**
	* Admin menu tooltip.
	*/
	function admin_notice_menu_tooltip() {

		$show_tooltip = get_option( 'ast_admin_menu_tooltip', 0 );				
	
		if ( $show_tooltip ) {
			// Dismissed less than 30 days ago.
			return;
		}
			
		$url = 'https://trackship.info/?utm_source=wpadmin&utm_campaign=astpopup';
		?>
		<div id="ast-admin-menu-tooltip" class="ast-admin-menu-tooltip-hide">
			<div class="ast-admin-menu-tooltip-header">
				<!--span class="ast-admin-menu-tooltip-icon"><span class="dashicons dashicons-megaphone"></span></span-->
				Automate your Post-Shipping Operations
				<a href="#" class="ast-admin-menu-tooltip-close"><span class="dashicons dashicons-dismiss"></span></a>
			</div>
			<div class="ast-admin-menu-tooltip-content">
				<img src="https://trackship.info/wp-content/uploads/2019/08/trackship-400.png" class="sidebar_ts_logo" style="max-width: 50%;">
				<p>TrackShip will auto-track your shipments with 140+ carriers around the globe and display real-time shipment status on your orders admin. You can automate the order management flow, direct customers to a tracking page on your store, automatically send shipment status notifications to your customer and more…</p>
				<p>
					<a href="<?php echo esc_url( $url ); ?>" target="blank" class="button button-primary btn_green2 btn_large">Try TrackShip for free!</a>
				</p>
			</div>
		</div>
		<style type="text/css">
			#ast-admin-menu-tooltip {
				position: absolute;
				left: 100%;
				top: 100%;
				background: #fff;
				margin-left: 16px;
				width: 350px;
				box-shadow: 0px 4px 7px 0px #ccc;
			}
	
			#ast-admin-menu-tooltip:before {
				content: '';
				width: 0;
				height: 0;
				border-style: solid;
				border-width: 12px 12px 12px 0;
				border-color: transparent #fff transparent transparent;
				position: absolute;
				right: 100%;
				top: 130px;
				z-index: 10;
			}
	
			#ast-admin-menu-tooltip:after {
				content: '';
				width: 0;
				height: 0;
				border-style: solid;
				border-width: 13px 13px 13px 0;
				border-color: transparent #ccc transparent transparent;
				position: absolute;
				right: 100%;
				margin-left: -1px;
				top: 129px;
				z-index: 5;
			}
	
			#ast-admin-menu-tooltip.ast-tooltip-arrow-top:before {
				top: 55px;
			}
	
			#ast-admin-menu-tooltip.ast-tooltip-arrow-top:after {
				top: 54px;
			}
	
			.ast-admin-menu-tooltip-header {
				background: #83bd31;
				padding: 10px 12px;
				font-size: 14px;
				font-weight: 700;
				font-family: Arial, Helvetica, "Trebuchet MS", sans-serif;
				color: #fff;
				line-height: 1.6;
			}
	
			.ast-admin-menu-tooltip-icon {
				background: #fff;
				border-radius: 50%;
				width: 28px;
				height: 25px;
				display: inline-block;
				color: #83bd31;
				text-align: center;
				padding: 3px 0 0;
				margin-right: 6px;
			}
	
			.ast-admin-menu-tooltip-hide {
				display: none;
			}
	
			.ast-admin-menu-tooltip-content {
				padding: 20px 15px 7px;
			}
	
			.ast-admin-menu-tooltip-content strong {
				font-size: 14px;
			}
	
			.ast-admin-menu-tooltip-content p strong {
				font-size: 13px;
			}
	
			.ast-admin-menu-tooltip-close {
				color: #fff;
				text-decoration: none;
				position: absolute;
				right: 10px;
				top: 14px;
				display: block;
			}
	
			.ast-admin-menu-tooltip-close:hover {
				color: #fff;
				text-decoration: none;
			}
	
			.ast-admin-menu-tooltip-close .dashicons {
				font-size: 14px;
			}
	
			@media ( max-width: 782px ) {
				#ast-admin-menu-tooltip {
					display: none;
				}
			}
		</style>
		<script type="text/javascript">
			if ( 'undefined' !== typeof jQuery ) {
				jQuery( function ( $ ) {
					var $tooltip = $( document.getElementById( 'ast-admin-menu-tooltip' ) );
					var $menuwrapper = $( document.getElementById( 'adminmenuwrap' ) );
					var $menuitem = $( document.getElementById( 'toplevel_page_woocommerce' ) );				
	
					if ( $menuitem.length ) {
						$menuwrapper.append( $tooltip );
						$tooltip.removeClass( 'ast-admin-menu-tooltip-hide' );
					}
	
					function alignTooltip() {
						var sticky = $( 'body' ).hasClass( 'sticky-menu' );
	
						var menuitem_pos = $menuitem.position();
						
						var tooltip_top = menuitem_pos.top - 124;					
												
						if ( sticky && $( window ).height() > $menuwrapper.height() + 150 ) {
							$tooltip.removeClass( 'ast-tooltip-arrow-top' );
						} else {
							tooltip_top = menuitem_pos.top - 50;
							$tooltip.addClass( 'ast-tooltip-arrow-top' );
						}
												
						$tooltip.css( {
							top: tooltip_top + 'px'
						} );
					}
	
					var $document = $( document );
					var timeout = setTimeout( alignTooltip, 10 );
					$document.on( 'wp-pin-menu wp-window-resized.pin-menu postboxes-columnchange.pin-menu postbox-toggled.pin-menu wp-collapse-menu.pin-menu wp-scroll-start.pin-menu', function () {
						if ( timeout ) {
							clearTimeout( timeout );
						}
						timeout = setTimeout( alignTooltip, 10 );
					} );
	
					$( '.ast-admin-menu-tooltip-close' ).on( 'click', function ( e ) {
						e.preventDefault();
						hideTooltip();
					} );
	
					function hideTooltip() {
						$tooltip.addClass( 'ast-admin-menu-tooltip-hide' );
						$.post( ajaxurl, {
							action: 'ast_hide_admin_menu_tooltip',
							nonce: '<?php echo esc_js( wp_create_nonce( 'ast-admin-tooltip-nonce' ) ); ?>',
						} );
					}
				} );
			}
		</script>
		<?php
	}	
	
	/**
	* Store the time when the float bar was hidden so it won't show again for 14 days.
	*/
	function ast_mark_admin_menu_tooltip_hidden() {
		check_ajax_referer( 'ast-admin-tooltip-nonce', 'nonce' );
		update_option( 'ast_admin_menu_tooltip', time() );
		wp_send_json_success();
	}
	
	/**
	* Create tracking page after store is connected
	*/
	public function create_tracking_page(){
		if(version_compare(get_option( 'wc_advanced_shipment_tracking_ts_page' ),'1.0', '<') ){
			$new_page_title = 'Shipment Tracking';
			$new_page_slug = 'ts-shipment-tracking';		
			$new_page_content = '[wcast-track-order]';       
			//don't change the code below, unless you know what you're doing
			$page_check = get_page_by_title($new_page_title);		
	
			if(!isset($page_check->ID)){
				$new_page = array(
					'post_type' => 'page',
					'post_title' => $new_page_title,
					'post_name' => $new_page_slug,
					'post_content' => $new_page_content,
					'post_status' => 'publish',
					'post_author' => 1,
				);
				$new_page_id = wp_insert_post($new_page);	
				update_option( 'wc_ast_trackship_page_id', $new_page_id );	
			}
			update_option( 'wc_advanced_shipment_tracking_ts_page', '1.0');					
		}	
	}
}