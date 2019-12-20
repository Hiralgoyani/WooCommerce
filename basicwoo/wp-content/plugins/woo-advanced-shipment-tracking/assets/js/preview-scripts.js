( function( $ ) {
	$('.hide').hide();
    /* Hide/Show Header */
	wp.customize( 'display_shipment_provider_image', function( value ) {		
		value.bind( function( to ) {
			if( to ){
				$( '.tracking-provider img' ).show();
			}	
			else{
				$( '.tracking-provider img' ).hide();					
			}
		});
	});
	wp.customize( 'remove_date_from_tracking', function( value ) {		
		value.bind( function( remove_date_from_tracking ) {
			if( remove_date_from_tracking ){
				$( '.date-shipped' ).hide();
			}	
			else{
				$( '.date-shipped' ).show();					
			}
		});
	});
	wp.customize( 'show_track_label', function( value ) {		
		value.bind( function( show_track_label ) {
			if( show_track_label ){
				$( 'span.track_label' ).show();
			}	
			else{
				$( 'span.track_label' ).hide();					
			}
		});
	});
	wp.customize( 'header_text_change', function( value ) {		
		value.bind( function( header_text ) {
			if( header_text ){
				$( '.header_text' ).text(header_text);
			} else{
				$( '.header_text' ).text('Tracking Information');
			}			
		});
	});
	wp.customize( 'additional_header_text', function( value ) {		
		value.bind( function( additional_header_text ) {
			if( additional_header_text ){
				$( '.addition_header' ).text(additional_header_text);
			} else{
				$( '.addition_header' ).text('');
			}			
		});
	});
	
	wp.customize( 'hide_table_header', function( value ) {		
		value.bind( function( hide_table_header ) {
			if( hide_table_header ){
				$( '.tracking_table thead' ).hide();
			}	
			else{
				$( '.tracking_table thead' ).show();					
			}		
		});
	});

	wp.customize( 'provider_header_text', function( value ) {		
		value.bind( function( provider_header_text ) {
			if( provider_header_text ){
				$( 'th.tracking-provider' ).text(provider_header_text);
			} else{
				$( 'th.tracking-provider' ).text('Provider');
			}			
		});
	});
	
	wp.customize( 'tracking_number_header_text', function( value ) {		
		value.bind( function( tracking_number_header_text ) {
			if( tracking_number_header_text ){
				$( 'th.tracking-number' ).text(tracking_number_header_text);
			} else{
				$( 'th.tracking-number' ).text('Tracking Number');
			}			
		});
	});
	
	wp.customize( 'shipped_date_header_text', function( value ) {		
		value.bind( function( shipped_date_header_text ) {
			if( shipped_date_header_text ){
				$( 'th.date-shipped ' ).text(shipped_date_header_text);
			} else{
				$( 'th.date-shipped ' ).text('Shipped Date');
			}			
		});
	});
	
	wp.customize( 'track_header_text', function( value ) {		
		value.bind( function( track_header_text ) {
			if( track_header_text ){
				$( 'th.order-actions' ).text(track_header_text);
			} else{
				$( 'th.order-actions' ).text('Track');
			}			
		});
	});
	
	
	wp.customize( 'header_content_text_align', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( header_content_text_align ) {			
			/* Update callback for setting change */
			$( '.tracking_table th' ).css( 'text-align',header_content_text_align );
			$( '.tracking_table td' ).css( 'text-align',header_content_text_align );			
		} );		
	} );
	
	wp.customize( 'table_padding', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( table_padding ) {			
			/* Update callback for setting change */
			$( '.tracking_table th' ).css( 'padding',table_padding+'px' );
			$( '.tracking_table td' ).css( 'padding',table_padding+'px' );			
		} );		
	} );
	
	wp.customize( 'table_bg_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( newValue ) {		
			/* Update callback for setting change */
			$( '.tracking_table' ).css( 'background-color',newValue );			
		} );		
	} );
	wp.customize( 'table_border_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( table_border_color ) {		
			/* Update callback for setting change */
			$( '.tracking_table th' ).css( 'border-color',table_border_color );
			$( '.tracking_table td' ).css( 'border-color',table_border_color );			
		} );		
	} );
	wp.customize( 'table_border_size', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( table_border_size ) {		
			/* Update callback for setting change */
			$( '.tracking_table th' ).css( 'border-width',table_border_size+'px' );
			$( '.tracking_table td' ).css( 'border-width',table_border_size+'px' );			
		} );		
	} );
	wp.customize( 'table_header_font_size', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( table_header_font_size ) {		
			/* Update callback for setting change */
			$( '.tracking_table th' ).css( 'font-size',table_header_font_size+'px' );			
		} );		
	} );
	wp.customize( 'table_header_font_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( table_header_font_color ) {		
			/* Update callback for setting change */
			$( '.tracking_table th' ).css( 'color',table_header_font_color );			
		} );		
	} );
	wp.customize( 'table_content_font_size', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( table_content_font_size ) {		
			/* Update callback for setting change */
			$( '.tracking_table td' ).css( 'font-size',table_content_font_size+'px' );			
		} );		
	} );
	wp.customize( 'table_content_font_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( table_content_font_color ) {		
			/* Update callback for setting change */
			$( '.tracking_table td' ).css( 'color',table_content_font_color );			
		} );		
	} );
	wp.customize( 'tracking_link_font_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tracking_link_font_color ) {		
			/* Update callback for setting change */
			$( '.tracking_table td a' ).css( 'color',tracking_link_font_color );			
		} );		
	} );
	wp.customize( 'tracking_link_bg_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tracking_link_bg_color ) {		
			/* Update callback for setting change */
			$( '.tracking_table td a' ).css( 'background-color',tracking_link_bg_color );			
		} );		
	} );
	wp.customize( 'tracking_link_border', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tracking_link_border ) {		
			/* Update callback for setting change */
			if( tracking_link_border ){
				$( '.tracking_table td a' ).css( 'text-decoration','underline' );
			}	
			else{
				$( '.tracking_table td a' ).css( 'text-decoration','unset' );
			}
		} );		
	} );
	wp.customize( 'table_content_line_height', function( value ) {		
		value.bind( function( table_content_line_height ) {
			$( '.tracking_table' ).css( 'line-height',table_content_line_height+'px' );
		});
	});	
	wp.customize( 'table_content_font_weight', function( value ) {		
		value.bind( function( table_content_font_weight ) {
			$( '.tracking_table td' ).css( 'font-weight',table_content_font_weight );
		});
	});
	
	wp.customize( 'woocommerce_customer_delivered_order_settings[heading]', function( value ) {		
		value.bind( function( wcast_delivered_email_heading ) {
					
			var str = wcast_delivered_email_heading;
			var res = str.replace("{site_title}", wcast_preview.site_title);
			
			var res = res.replace("{order_number}", wcast_preview.order_number);
				
			if( wcast_delivered_email_heading ){				
				$( '#header_wrapper h1' ).text(res);
			} else{
				$( '#header_wrapper h1' ).text('');
			}			
		});
	});
	
	wp.customize( 'woocommerce_customer_partial_shipped_order_settings[heading]', function( value ) {		
		value.bind( function( wcast_partial_shipped_email_heading ) {
					
			var str = wcast_partial_shipped_email_heading;
			var res = str.replace("{site_title}", wcast_preview.site_title);
			
			var res = res.replace("{order_number}", wcast_preview.order_number);
				
			if( wcast_partial_shipped_email_heading ){				
				$( '#header_wrapper h1' ).text(res);
			} else{
				$( '#header_wrapper h1' ).text('');
			}			
		});
	});
	
	
	wp.customize( 'remove_trackship_branding', function( value ) {		
		value.bind( function( remove_trackship_branding ) {
			if( remove_trackship_branding ){
				$( '.trackship_branding' ).hide();
			}	
			else{
				$( '.trackship_branding' ).show();					
			}
		});
	});
	
	wp.customize( 'tpage_primary_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tpage_primary_color ) {		
			/* Update callback for setting change */
			$( '.bg-secondary' ).css( 'background-color',tpage_primary_color );
			$( '.tracker-progress-bar-with-dots .secondary .dot' ).css( 'border-color',tpage_primary_color );
			$( '.text-secondary' ).css( 'color',tpage_primary_color );			
		} );		
	} );
	
	wp.customize( 'tpage_success_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tpage_success_color ) {		
			/* Update callback for setting change */
			$( '.bg-success' ).css( 'background-color',tpage_success_color );
			$( '.tracker-progress-bar-with-dots .success .dot' ).css( 'border-color',tpage_success_color );
			$( '.text-success' ).css( 'color',tpage_success_color );			
		} );		
	} );
	
	wp.customize( 'tpage_warning_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tpage_warning_color ) {		
			/* Update callback for setting change */
			$( '.bg-warning' ).css( 'background-color',tpage_warning_color );
			$( '.tracker-progress-bar-with-dots .warning .dot' ).css( 'border-color',tpage_warning_color );
			$( '.text-warning' ).css( 'color',tpage_warning_color );			
		} );		
	} );
	
	wp.customize( 'tpage_border_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tpage_border_color ) {		
			/* Update callback for setting change */
			$( '.col.tracking-detail' ).css( 'border','1px solid'+tpage_border_color );			
		} );		
	} );
	
	wp.customize( 'tracking_info_width', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tracking_info_width ) {		
			/* Update callback for setting change */
			$( '.col.tracking-detail' ).css( 'width',tracking_info_width+'px' );			
		} );		
	} );
	
	wp.customize( 'table_margin_top_bottom', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( table_margin_top_bottom ) {		
			/* Update callback for setting change */
			$( '.col.tracking-detail' ).css( 'margin',table_margin_top_bottom+'px auto' );			
		} );		
	} );
	
	wp.customize( 'tdetails_border_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tdetails_border_color ) {		
			/* Update callback for setting change */
			$( '.tracking-details' ).css( 'border-color',tdetails_border_color );			
		} );		
	} );
	
	wp.customize( 'tinfo_shade_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tinfo_shade_color ) {		
			/* Update callback for setting change */
			$( '.bg-gray-100' ).css( 'background-color',tinfo_shade_color );			
		} );		
	} );
	
	wp.customize( 'tevents_font_color', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( tevents_font_color ) {		
			/* Update callback for setting change */
			$( '.text-gray-300' ).css( 'color',tevents_font_color );			
		} );		
	} );	
	
	wp.customize( 'wcast_failure_email_heading', function( value ) {		
		value.bind( function( wcast_failure_email_heading ) {
					
			var str = wcast_failure_email_heading;
			var res = str.replace("{site_title}", wcast_preview.site_title);
			
			var res = res.replace("{order_number}", wcast_preview.order_number);
				
			if( wcast_failure_email_heading ){				
				$( '#header_wrapper h1' ).text(res);
			} else{
				$( '#header_wrapper h1' ).text('');
			}			
		});
	});
} )( jQuery );