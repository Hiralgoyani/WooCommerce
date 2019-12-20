jQuery( document ).ready( function( $ ) {
	'use strict';

	// create namespace to avoid any possible conflicts
	$.WooSquare_admin = {

		/**
		 * Loops through the sync process
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 * @param int process the current step in the loop
		 * @param string type the type of the AJAX call
		 */
		sync: function( process, type ) {
			var data = {
					ajaxSyncNonce: WooSquare_local.ajaxSyncNonce,
					action: 'wc-to-square' === type ? 'wc_to_square_ajax' : 'square_to_wc_ajax',
					process: parseInt( process, 10 )
				};

			$.post( WooSquare_local.ajaxurl, data, function( response ) {

				if ( 'done' === response.process ) {
					// triggers when all processing is done
					$( 'body' ).trigger( 'woocommerce_square_wc_to_square_sync_complete', [ response ] );

					$( 'table.form-table' ).unblock();

					$( '.wp-square-progress-bar span' ).animate( { width: response.percentage + '%' }, 30, 'swing', function() { 
						$( '.wp-square-progress-bar' ).fadeOut( 'slow', function() { 
							alert( response.message );
						});
					});

				} else {
					$( '.wp-square-progress-bar span' ).animate( { width: response.percentage + '%' }, 30, 'swing' );

					$.WooSquare_admin.sync( parseInt( response.process, 10 ), response.type );
				}
			});
		},

		init: function() {

			$( '.woocommerce_page_wc-settings' ).on( 'click', '#wc-to-square, #square-to-wc', function( e ) {
				e.preventDefault();

				var page = $( this ).parents( 'table.form-table' ),
					progress_bar = $( '<div class="wp-square-progress-bar wp-square-stripes"><span class="step"></span></div>' );

				// remove the progress bar on each trigger
				$( '.wp-square-progress-bar' ).remove();

				page.block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});

				// add the progress bar
				page.after( progress_bar );

				// initially move the progress bar to show progression
				$( '.wp-square-progress-bar span' ).animate( { width: '1%' }, 30, 'swing' );

				$.WooSquare_admin.sync( 0, $( this ).attr( 'id' ) );
			});

			$( document.body ).on( 'change', '#woocommerce_square_testmode', function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '#woocommerce_square_application_id' ).parents( 'tr' ).eq(0).hide();
					$( '#woocommerce_square_token' ).parents( 'tr' ).eq(0).hide();

					$( '#woocommerce_square_sandbox_application_id' ).parents( 'tr' ).eq(0).show();
					$( '#woocommerce_square_sandbox_token' ).parents( 'tr' ).eq(0).show();
				} else {
					$( '#woocommerce_square_application_id' ).parents( 'tr' ).eq(0).show();
					$( '#woocommerce_square_token' ).parents( 'tr' ).eq(0).show();

					$( '#woocommerce_square_sandbox_application_id' ).parents( 'tr' ).eq(0).hide();
					$( '#woocommerce_square_sandbox_token' ).parents( 'tr' ).eq(0).hide();
				}
			});

			$( '#woocommerce_square_testmode' ).trigger( 'change' );

			$( document.body ).on( 'change', '#woocommerce_squareconnect_sync_products', function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '#woocommerce_squareconnect_sync_categories' ).parents( 'tr' ).eq(0).show();
					$( '#woocommerce_squareconnect_sync_inventory' ).parents( 'tr' ).eq(0).show();
					$( '#woocommerce_squareconnect_sync_images' ).parents( 'tr' ).eq(0).show();
				} else {
					$( '#woocommerce_squareconnect_sync_categories' ).parents( 'tr' ).eq(0).hide();
					$( '#woocommerce_squareconnect_sync_inventory' ).parents( 'tr' ).eq(0).hide();
					$( '#woocommerce_squareconnect_sync_images' ).parents( 'tr' ).eq(0).hide();
				}
			});

			$( '#woocommerce_squareconnect_sync_products' ).trigger( 'change' );
		}
	}; // close namespace

	$.WooSquare_admin.init();
// end document ready
});
