/*
 * Customizer Scripts
 * Need to rewrite and clean up this file.
 */

jQuery(document).ready(function() {

    /**
     * Change description
     */
	jQuery(wcast_customizer.trigger_click).trigger( "click" );    
	jQuery('#customize-theme-controls #accordion-section-themes').hide();
	
	if(wcast_customizer.wcast_enable_delivered_email == 'yes'){
		jQuery('#_customize-input-wcast_enable_delivered_status_email').prop('disabled', true);	   		
	}

	if(jQuery("#_customize-input-show_track_label").prop("checked") != true){	
		jQuery('#customize-control-track_header_text').hide();
	}
	
	if(jQuery("#_customize-input-wcast_enable_delivered_ga_tracking").prop("checked") != true){	
		jQuery('#customize-control-wcast_delivered_analytics_link').hide();
	}
	if(jQuery('#_customize-input-hide_table_header').prop("checked") == true){
		jQuery('#customize-control-provider_header_text').hide();
		jQuery('#customize-control-tracking_number_header_text').hide();
		jQuery('#customize-control-shipped_date_header_text').hide();
		jQuery('#customize-control-show_track_label').hide();		
		jQuery('#customize-control-track_header_text').hide();
		jQuery('#customize-control-table_header_font_size').hide();
		jQuery('#customize-control-table_header_font_color').hide(); 
	} else{
		jQuery('#customize-control-provider_header_text').show();
		jQuery('#customize-control-tracking_number_header_text').show();
		jQuery('#customize-control-shipped_date_header_text').show();		
		jQuery('#customize-control-track_header_text').show();
		jQuery('#customize-control-table_header_font_size').show();
		jQuery('#customize-control-table_header_font_color').show(); 
		if(jQuery("#_customize-input-show_track_label").prop("checked") == true){	
			jQuery('#customize-control-track_header_text').show();
		} else{
			jQuery('#customize-control-track_header_text').hide();
		}
	}

});
jQuery(document).on("change", "#_customize-input-show_track_label", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-track_header_text').show();
	} else{
		jQuery('#customize-control-track_header_text').hide();
	}
});
jQuery(document).on("change", "#_customize-input-hide_table_header", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-provider_header_text').hide();
		jQuery('#customize-control-tracking_number_header_text').hide();
		jQuery('#customize-control-shipped_date_header_text').hide();
		jQuery('#customize-control-show_track_label').hide();
		jQuery('#customize-control-show_track_label').hide();
		jQuery('#customize-control-track_header_text').hide();
		jQuery('#customize-control-table_header_font_size').hide();
		jQuery('#customize-control-table_header_font_color').hide(); 
	} else{
		jQuery('#customize-control-provider_header_text').show();
		jQuery('#customize-control-tracking_number_header_text').show();
		jQuery('#customize-control-shipped_date_header_text').show();
		jQuery('#customize-control-show_track_label').show();
		jQuery('#customize-control-track_header_text').show();
		jQuery('#customize-control-table_header_font_size').show();
		jQuery('#customize-control-table_header_font_color').show(); 
		if(jQuery("#_customize-input-show_track_label").prop("checked") == true){	
			jQuery('#customize-control-track_header_text').show();
		}
	}
});
jQuery(document).on("change", "#_customize-input-wcast_enable_delivered_ga_tracking", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-wcast_delivered_analytics_link').show();
	} else{
		jQuery('#customize-control-wcast_delivered_analytics_link').hide();
	}
});	
jQuery(document).on("change", "#customize-control-woocommerce_customer_delivered_order_settings-enabled input", function(){	
	if(jQuery(this).prop("checked") == true){
		jQuery('#_customize-input-wcast_enable_delivered_status_email').prop('disabled', true);
	} else{
		jQuery('#_customize-input-wcast_enable_delivered_status_email').removeAttr('disabled');
	}
});
    // Handle mobile button click
    function custom_size_mobile() {
    	// get email width.
    	var email_width = '684';
    	var ratio = email_width/304;
    	var framescale = 100/ratio;
    	var framescale = framescale/100;
    	jQuery('#customize-preview iframe').width(email_width+'px');
    	jQuery('#customize-preview iframe').css({
				'-webkit-transform' : 'scale(' + framescale + ')',
				'-moz-transform'    : 'scale(' + framescale + ')',
				'-ms-transform'     : 'scale(' + framescale + ')',
				'-o-transform'      : 'scale(' + framescale + ')',
				'transform'         : 'scale(' + framescale + ')'
		});
    }
	jQuery('#customize-footer-actions .preview-mobile').click(function(e) {
		custom_size_mobile();
	});
		jQuery('#customize-footer-actions .preview-desktop').click(function(e) {
		jQuery('#customize-preview iframe').width('100%');
		jQuery('#customize-preview iframe').css({
				'-webkit-transform' : 'scale(1)',
				'-moz-transform'    : 'scale(1)',
				'-ms-transform'     : 'scale(1)',
				'-o-transform'      : 'scale(1)',
				'transform'         : 'scale(1)'
		});
	});
	jQuery('#customize-footer-actions .preview-tablet').click(function(e) {
		jQuery('#customize-preview iframe').width('100%');
		jQuery('#customize-preview iframe').css({
				'-webkit-transform' : 'scale(1)',
				'-moz-transform'    : 'scale(1)',
				'-ms-transform'     : 'scale(1)',
				'-o-transform'      : 'scale(1)',
				'transform'         : 'scale(1)'
		});
	});
	
(function ( api ) {
    api.section( 'customer_delivered_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {	
			
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'customer_partial_shipped_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.partial_shipped_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'default_controls_section', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.tracking_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'customer_failure_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_failure_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_intransit_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_intransit_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_outfordelivery_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_outfordelivery_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_delivered_status_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_delivered_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_returntosender_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_returntosender_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_availableforpickup_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_availableforpickup_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

jQuery(document).on("change", ".preview_order_select", function(){
	var wcast_preview_order_id = jQuery(this).val();
	var data = {
		action: 'update_email_preview_order',
		wcast_preview_order_id: wcast_preview_order_id,	
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: data,
		type: 'POST',
		success: function(response) {			
			jQuery(".preview_order_select option[value="+wcast_preview_order_id+"]").attr('selected', 'selected');			
		},
		error: function(response) {
			console.log(response);			
		}
	});	
});