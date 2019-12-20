( function( $, data, wp, ajaxurl ) {
	jQuery(".custom_provider_country").select2();
	var wc_table_rate_rows_row_template = wp.template( 'shipping-provider-row-template' ),
		$rates_table                    = $( '#shipping_rates' ),
		$rates                          = $rates_table.find( 'tbody.table_rates' ),
		$table = $(".shipping_provider_table");
		
	var $wc_ast_settings_form = $("#wc_ast_settings_form");
	var $wc_ast_trackship_form = $("#wc_ast_trackship_form");
	var $wc_ast_addons_form = $("#wc_ast_addons_form");
		
	
	var wc_table_rate_rows = {
		
		init: function() {
			
			$(document).on( 'click', 'a.add-provider', this.onAddProvider )
						.on( 'click', '.shipping_provider_table .remove', this.onRemoveProvider );			

			var rates_data = $rates.data( 'rates' );
			
			$( rates_data ).each( function( i ) {
				var size = $rates.find( '.table_rate' ).length;
				$rates.append( wc_table_rate_rows_row_template( {
					rate:  rates_data[ i ],
					index: size
				} ) );
			} );
			
			$wc_ast_settings_form.on( 'click', '.woocommerce-save-button', this.save_wc_ast_settings_form );			
			$wc_ast_trackship_form.on( 'click', '.woocommerce-save-button', this.save_wc_ast_trackship_form );
			$wc_ast_addons_form.on( 'click', '.woocommerce-save-button', this.save_wc_ast_addons_form );
			
			$(".tipTip").tipTip();

		},

		save_wc_ast_settings_form: function( event ) {
			event.preventDefault();
			
			$wc_ast_settings_form.find(".spinner").addClass("active");
			var ajax_data = $wc_ast_settings_form.serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {
				$wc_ast_settings_form.find(".spinner").removeClass("active");
				var snackbarContainer = document.querySelector('#demo-toast-example');
				var data = {message: shipment_tracking_table_rows.i18n.data_saved };
				snackbarContainer.MaterialSnackbar.showSnackbar(data);
			});
			
		},				
		
		save_wc_ast_trackship_form: function( event ) {
			event.preventDefault();
			
			$wc_ast_trackship_form.find(".spinner").addClass("active");
			//$wc_ast_settings_form.find(".success_msg").hide();
			var ajax_data = $wc_ast_trackship_form.serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {
				$wc_ast_trackship_form.find(".spinner").removeClass("active");
				var snackbarContainer = document.querySelector('#demo-toast-example');
				var data = {message: 'Data saved successfully.'};
				snackbarContainer.MaterialSnackbar.showSnackbar(data);	
				//$wc_ast_settings_form.find(".success_msg").show();
			});
			
		},
		
		save_wc_ast_addons_form: function( event ) {
			event.preventDefault();
			
			$wc_ast_addons_form.find(".spinner").addClass("active");
			jQuery('.pesan').hide();
			var action = jQuery('#ast-license-action').val();
			//$wc_ast_settings_form.find(".success_msg").hide();
			var ajax_data = $wc_ast_addons_form.serialize();
			
			$.post( ajaxurl, ajax_data, function(data) {
				$wc_ast_addons_form.find(".spinner").removeClass("active");
				jQuery('.pesan').show();
				//console.log(data);
				var btn_value = 'Activate';
				if(data.success == 'true'){
					if(action == 'ast_product_license_activate'){
						var btn_value = 'Deactivate';
						jQuery('#ast-license-action').val('esre_license_deactivate');
						jQuery('.pesan').html('Congratulation, your license successful activated');
						jQuery('.activated').show();
						window.location.reload();
					} else {
						jQuery('#ast-license-action').val('esre_license_activate');
						jQuery('#ast_product_license_key').val('');
						jQuery('#ast_product_license_email').val('');
						jQuery('.pesan').html('<span style="color:green;">Congratulation, your license successful deactivated</span>');
						jQuery('.activated').hide();
						window.location.reload();				
					}
				} else {
					jQuery('.pesan').html('<span style="color:red;">'+data.message+'</span>');
				}
				
				jQuery('#saveS').prop('disabled', false).val(btn_value);				
				//$wc_ast_settings_form.find(".success_msg").show();
			});
			
		},
		
		onAddProvider: function( event ) {
			
			event.preventDefault();
			var target = $table;
			
			var ajax_data = {
				action: 'woocommerce_shipping_provider_add',
				security: data.delete_rates_nonce,
				
			};
			var sort_id = $('.shipping_provider_table  tbody tr.provider_tr:last').find('.sort_order').val();
			$.ajax({
				url: ajaxurl,
				dataType: "json",
				data:ajax_data,
				success: function(response) {
					
					target.find("tbody").append( wc_table_rate_rows_row_template( {
						rate:  {
							id: '',
							provider_name: '',
							shipping_country: '',
							provider_url: ''
						},
						index: response.id,
						sort_id: (Number(sort_id) + 1),
					} ) );
					jQuery(".wcast_shipping_country").select2();					
				},
				error: function(response) {
					console.log(response);					
				}
			});
			
		},
		onRemoveProvider: function( event ) {
			event.preventDefault();
			$(".shipping_provider_table ").block({
			message: null,
			overlayCSS: {
				background: "#fff",
				opacity: .6
			}	
			});	

			var r = confirm( shipment_tracking_table_rows.i18n.delete_provider );
			if (r === true) {
				
			} else {
				$(".shipping_provider_table ").unblock();	
				return;
			}
			
			var provider_row = jQuery(this).parents("tr");
			var provider_id = jQuery(this).data("pid");
			
			var ajax_data = {
				action: 'woocommerce_shipping_provider_delete',
				provider_id: provider_id,
			};

			$.post( ajaxurl, ajax_data, function(response) {
				provider_row.remove();
				update_default_shipping_provider();
				$(".shipping_provider_table ").unblock();	
			});
		}
	};
	$(window).load(function(e) {
        wc_table_rate_rows.init();
    });
})( jQuery, shipment_tracking_table_rows, wp, ajaxurl );


jQuery(document).on("change", ".wc_ast_default_provider", function(){
	jQuery(".d_s_select_section ").block({
    message: null,
    overlayCSS: {
        background: "#fff",
        opacity: .6
	}	
    });
	var default_provider = jQuery('.wc_ast_default_provider').val();
	var ajax_data = {
		action: 'update_default_provider',
		default_provider: default_provider,		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery(".d_s_select_section ").unblock();
			var snackbarContainer = document.querySelector('#demo-toast-example');
			var data = {message: shipment_tracking_table_rows.i18n.data_saved};
			snackbarContainer.MaterialSnackbar.showSnackbar(data);			
		},
		error: function(response) {					
		}
	});
});
	var file_frame;
	jQuery('#upload_image_button').live('click', function(product) {
		product.preventDefault();
		var image_id = jQuery(this).siblings(".image_id");
		var image_path = jQuery(this).siblings(".image_path");
		
		// If the media frame already exists, reopen it.
		if (file_frame) {
			file_frame.open();
			return;
		}
	
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Upload Media',
			button: {
				text: 'Add',
			},
			multiple: false // Set to true to allow multiple files to be selected
		});
	
		// When a file is selected, run a callback.
		file_frame.on('select', function(){     
			attachment = file_frame.state().get('selection').first().toJSON();       
			var id = attachment.id;        
			var url = attachment.url;     
			image_path.attr('value', url);
			image_id.attr('value', id);
	
		});
		// Finally, open the modal
		file_frame.open();
	});
jQuery(document).on("submit", "#wc_ast_upload_csv_form", function(){
	jQuery('.csv_upload_status li').remove();	
	jQuery('.bulk_upload_status_tr').hide();
	jQuery('.progress_title').hide();	
	var form = jQuery('#wc_ast_upload_csv_form');	
	var error;
	var trcking_csv_file = form.find("#trcking_csv_file");
	var replace_tracking_info = jQuery("#replace_tracking_info").prop("checked");
	if(replace_tracking_info == true){
		replace_tracking_info = 1;
	} else{
		replace_tracking_info = 0;
	}
	
	
	
	var ext = jQuery('#trcking_csv_file').val().split('.').pop().toLowerCase();	
	
	if( trcking_csv_file.val() === '' ){		
		showerror( trcking_csv_file );
		error = true;
	} else{
		if(ext != 'csv'){
			alert(shipment_tracking_table_rows.i18n.upload_only_csv_file);	
			showerror( trcking_csv_file );
			error = true;
		} else{
			hideerror(trcking_csv_file);
		}
	}
	
	if(error == true){
		return false;
	}
	

             var regex = /([a-zA-Z0-9\s_\\.\-\(\):])+(.csv|.txt)$/;
             if (regex.test(jQuery("#trcking_csv_file").val().toLowerCase())) {
                 if (typeof (FileReader) != "undefined") {
                     var reader = new FileReader();
                     reader.onload = function (e) {
                         var trackings = new Array();
                         var rows = e.target.result.split("\r\n");	
						 if(rows.length <= 1){
							 alert('There are some issue with CSV file.');
							 return false;
						 }		
                         for (var i = 1; i < rows.length; i++) {
                             var cells = rows[i].split(",");
                             if (cells.length > 1) {
                                 var tracking = {};
                                 tracking.order_id = cells[0];								 
                                 tracking.tracking_provider = cells[1];
                                 tracking.tracking_number = cells[2];
								 tracking.date_shipped = cells[3];
								 tracking.status_shipped = cells[4];
								 if(tracking.order_id){
									trackings.push(tracking);	
								 }						
                             }
                         }  
				var csv_length = trackings.length;
				
				jQuery("#wc_ast_upload_csv_form")[0].reset();
				jQuery("#p1 .progressbar").css('background-color','rgb(63,81,181)');
				var querySelector = document.querySelector('#p1');
				querySelector.MaterialProgress.setProgress(0);
				jQuery("#p1").show();
                jQuery(trackings).each(function(index, element) {
					
					var order_id = trackings[index]['order_id'];
					var tracking_provider = trackings[index]['tracking_provider'];
					var tracking_number = trackings[index]['tracking_number'];
					var date_shipped = trackings[index]['date_shipped'];
					var status_shipped = trackings[index]['status_shipped'];													
					
					var data = {
							action: 'wc_ast_upload_csv_form_update',
							order_id: order_id,
							tracking_provider: tracking_provider,
							tracking_number: tracking_number,
							date_shipped: date_shipped,
							status_shipped: status_shipped,
							replace_tracking_info: replace_tracking_info,
							trackings: trackings,	
						};
				
					var option = {
				
						url: ajaxurl,
						data: data,
						type: 'POST',
						success:function(data){	
							//alert(data);
							jQuery('.progress_number').html((index+1)+'/'+csv_length);
							
							jQuery('.csv_upload_status').append(data);
							var progress = (index+1)*100/csv_length;
							jQuery('.bulk_upload_status_tr').show();
							jQuery('.progress_title').show();	
							querySelector.MaterialProgress.setProgress(progress);
							if(progress == 100){
								jQuery("#p1 .progressbar").css('background-color','green');
								var snackbarContainer = document.querySelector('#demo-toast-example');
								var data = {message: shipment_tracking_table_rows.i18n.data_saved};
								snackbarContainer.MaterialSnackbar.showSnackbar(data);
									
							}												
						},
				
					};
				
					jQuery.ajaxQueue.addRequest(option);
				
					jQuery.ajaxQueue.run();					
				
				});
                     }
                     reader.readAsText(jQuery("#trcking_csv_file")[0].files[0]);
			
			
                 } else {
                     alert(shipment_tracking_table_rows.i18n.browser_not_html);
                 }
             } else {
                 alert(shipment_tracking_table_rows.i18n.upload_valid_csv_file);
             }
	return false;
});
 

jQuery(document).on("change", ".shipment_status_toggle input", function(){
	jQuery("#content5 ").block({
    message: null,
    overlayCSS: {
        background: "#fff",
        opacity: .6
	}	
    });
	if(jQuery(this).prop("checked") == true){
		var wcast_enable_status_email = 1;
	}
	var id = jQuery(this).attr('id');
	var ajax_data = {
		action: 'update_shipment_status_email_status',
		id: id,
		wcast_enable_status_email: wcast_enable_status_email,		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery("#content5 ").unblock();
			var snackbarContainer = document.querySelector('#demo-toast-example');
			var data = {message: shipment_tracking_table_rows.i18n.data_saved};
			snackbarContainer.MaterialSnackbar.showSnackbar(data);			
		},
		error: function(response) {					
		}
	});
});


jQuery(document).on("click", ".status_filter a", function(){
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	jQuery('.status_filter a').removeClass('active');
	jQuery(this).addClass('active');
	var status = jQuery(this).data('status');
	var ajax_data = {
		action: 'filter_shipiing_provider_by_status',
		status: status,		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery(".provider_list").replaceWith(response);	
			jQuery("#content1 ").unblock();			
			componentHandler.upgradeAllRegistered();			
		},
		error: function(response) {					
		}
	});
});

jQuery(document).on("click", ".status_slide", function(){
	var id = jQuery(this).val();
	if(jQuery(this).prop("checked") == true){
       var checked = 1;
	   jQuery(this).closest('.provider').addClass('active_provider');
	   jQuery('#make_default_'+id).prop('disabled', false);
	   jQuery('#default_label_'+id).removeClass('disable_label');
    } else{
		var checked = 0;
		jQuery(this).closest('.provider').removeClass('active_provider');
		jQuery('#make_default_'+id).prop('disabled', true);
		jQuery('#make_default_'+id).prop('checked', false);
		jQuery('#default_label_'+id).addClass('disable_label');
	}
	

	var error;	
	var ajax_data = {
		action: 'update_shipment_status',
		id: id,
		checked: checked,	 
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {						
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("change", ".make_provider_default", function(){	
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	if(jQuery(this).prop("checked") == true){
	   jQuery('.make_provider_default').removeAttr('checked');
       var checked = 1;	   
	   jQuery(this).prop('checked',true);	   
    } else{
		var checked = 0;		
	}
	var id = jQuery(this).data('id');
	
	var error;	
	var default_provider = jQuery(this).val();
	var ajax_data = {
		action: 'update_default_provider',
		default_provider: default_provider,	
		id: id,
		checked: checked,			
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery("#content1 ").unblock();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on( "input", "#search_provider", function(){	
	jQuery('.status_filter a').removeClass('active');
	jQuery("[data-status=all]").addClass('active');	
	
	var ajax_data = {
		action: 'filter_shipiing_provider_by_status',
		status: 'all',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery(".provider_list").replaceWith(response);				
			componentHandler.upgradeAllRegistered();		
			var provider_found = false;	
			var searchvalue = jQuery("#search_provider").val().toLowerCase().replace(/\s+/g, '');
			jQuery('.provider').each(function() {
				var provider = jQuery(this).find('.provider_name').text().toLowerCase().replace(/\s+/g, '');		
				var country = jQuery(this).find('.provider_country').text().toLowerCase().replace(/\s+/g, '');
				
				var hasprovider = provider.indexOf(searchvalue)!==-1;
				var hascountry= country.indexOf(searchvalue)!==-1;
				
				if (hasprovider || hascountry) {			
					jQuery(this).show();
					provider_found = true;	
				} else {					
					jQuery(this).hide();
				}
			});	
			if(provider_found == false){
				jQuery(".provider_list").append('<h3 class="not_found_label">No Shipping Providers Found.</h3>');
			} else{
				jQuery(".not_found_label").remove();
			}
		},
		error: function(response) {					
		}
	});	
});

jQuery(document).on("click", ".add_custom_provider", function(){	
	jQuery('.add_provider_popup').show();
});
jQuery(document).on("click", ".popupclose", function(){
	jQuery('.add_provider_popup').hide();
	jQuery('.edit_provider_popup').hide();
	jQuery('.sync_provider_popup').hide();
	jQuery('.how_to_video_popup').hide();
	jQuery('.ts_video_popup').hide();
});
jQuery(document).on("click", ".close_synch_popup", function(){		
	jQuery('.sync_provider_popup').hide();
	jQuery(".sync_message").show();
	jQuery(".synch_result").hide();
	jQuery(".view_synch_details").remove();
	jQuery(".updated_details").remove();	
	
	jQuery(".sync_providers_btn").show();
	jQuery(".close_synch_popup").hide();
});
 jQuery(document).on("submit", "#add_provider_form", function(){
	
	var form = jQuery('#add_provider_form');
	var error;
	var shipping_provider = jQuery(".add_provider_popup #shipping_provider");
	var shipping_country = jQuery(".add_provider_popup #shipping_country");
	var thumb_url = jQuery(".add_provider_popup #thumb_url");
	var tracking_url = jQuery(".add_provider_popup #tracking_url");	
	
	if( shipping_provider.val() === '' ){				
		showerror(shipping_provider);
		error = true;
	} else{		
		hideerror(shipping_provider);
	}	
	
	if( shipping_country.val() === '' ){				
		showerror(shipping_country);
		error = true;
	} else{		
		hideerror(shipping_country);
	}	
	
	/*if( thumb_url.val() === '' ){				
		showerror(thumb_url);
		error = true;
	} else{		
		hideerror(thumb_url);
	}
	
	if( tracking_url.val() === '' ){				
		showerror(tracking_url);
		error = true;
	} else{		
		hideerror(tracking_url);
	}*/
	
	
	if(error == true){
		return false;
	}	
	jQuery(".add_provider_popup").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),
		type: 'POST',		
		success: function(response) {					
			jQuery(".provider_list").replaceWith(response);	
			form[0].reset();						
			componentHandler.upgradeAllRegistered();
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=custom]").addClass('active');	
			jQuery('.add_provider_popup').hide();			
			jQuery(".add_provider_popup").unblock();
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".remove", function(){	
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	var r = confirm( shipment_tracking_table_rows.i18n.delete_provider );
	if (r === true) {		
	} else {
		jQuery("#content1").unblock();	
		return;
	}
	var id = jQuery(this).data('pid');
	
	var error;	
	var default_provider = jQuery(this).val();
	var ajax_data = {
		action: 'woocommerce_shipping_provider_delete',		
		provider_id: id,
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery(".provider_list").replaceWith(response);
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=custom]").addClass('active');	
			componentHandler.upgradeAllRegistered();
			jQuery("#content1").unblock();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", ".edit_provider", function(){		
	var id = jQuery(this).data('pid');
	var ajax_data = {
		action: 'get_provider_details',		
		provider_id: id,
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		dataType: "json",
		success: function(response) {
			var provider_name = response.provider_name;
			var provider_url = response.provider_url;
			var shipping_country = response.shipping_country;
			var custom_thumb_id = response.custom_thumb_id;
			var image = response.image;
			jQuery('.edit_provider_popup #shipping_provider').val(provider_name);
			jQuery('.edit_provider_popup #tracking_url').val(provider_url);
			jQuery('.edit_provider_popup #thumb_url').val(image);
			jQuery('.edit_provider_popup #thumb_id').val(custom_thumb_id);
			jQuery('.edit_provider_popup #provider_id').val(id);
			jQuery(".edit_provider_popup #shipping_country").val(shipping_country);
			jQuery('.edit_provider_popup').show();	
			//console.log(provider_name);	
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("submit", "#edit_provider_form", function(){
	
	var form = jQuery('#edit_provider_form');
	var error;
	var shipping_provider = jQuery("#edit_provider_form #shipping_provider");
	var shipping_country = jQuery("#edit_provider_form #shipping_country");
	var thumb_url = jQuery("#edit_provider_form #thumb_url");
	var tracking_url = jQuery("#edit_provider_form #tracking_url");	
	
	if( shipping_provider.val() === '' ){				
		showerror(shipping_provider);
		error = true;
	} else{		
		hideerror(shipping_provider);
	}	
	
	if( shipping_country.val() === '' ){				
		showerror(shipping_country);
		error = true;
	} else{		
		hideerror(shipping_country);
	}		
	
	/*if( tracking_url.val() === '' ){				
		showerror(tracking_url);
		error = true;
	} else{		
		hideerror(tracking_url);
	}*/
	
	
	if(error == true){
		return false;
	}	
	jQuery(".edit_provider_popup").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),
		type: 'POST',		
		success: function(response) {					
			jQuery(".provider_list").replaceWith(response);	
			form[0].reset();						
			componentHandler.upgradeAllRegistered();
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=custom]").addClass('active');				
			jQuery('.edit_provider_popup').hide();			
			jQuery(".edit_provider_popup").unblock();
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".reset_active", function(){	
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	var r = confirm( 'Do you really want to change all provider status to active?' );
	if (r === true) {		
	} else {
		jQuery("#content1").unblock();	
		return;
	}
		
	var error;		
	var ajax_data = {
		action: 'update_provider_status_active',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery(".provider_list").replaceWith(response);
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=active]").addClass('active');	
			componentHandler.upgradeAllRegistered();
			jQuery("#content1").unblock();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", ".reset_inactive", function(){	
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	var r = confirm( 'Do you really want to change all provider status to inactive?' );
	if (r === true) {		
	} else {
		jQuery("#content1").unblock();	
		return;
	}
		
	var error;		
	var ajax_data = {
		action: 'update_provider_status_inactive',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery(".provider_list").replaceWith(response);
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=inactive]").addClass('active');	
			componentHandler.upgradeAllRegistered();
			jQuery("#content1").unblock();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", ".sync_providers", function(){		
	jQuery('.sync_provider_popup').show();				
});
jQuery(document).on("click", ".sync_providers_btn", function(){	
	jQuery('.sync_provider_popup .spinner').addClass('active');
	jQuery('.sync_message').hide();
	var ajax_data = {
		action: 'sync_providers',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		dataType: "json",
		success: function(response) {			
			jQuery('.sync_provider_popup .spinner').removeClass('active');			
			jQuery(".provider_list").replaceWith(response.html);
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=active]").addClass('active');
			
			if(response.sync_error == 1 ){
				jQuery( ".sync_message" ).text( response.message );
				jQuery( ".sync_providers_btn" ).text( 'Retry' );				
			} else{
				jQuery(".providers_added span").text(response.added);
				if(response.added > 0 ){
					jQuery( ".providers_added" ).append( response.added_html );
				}
				
				jQuery(".providers_updated span").text(response.updated);
				if(response.updated > 0 ){
					jQuery( ".providers_updated" ).append( response.updated_html );
				}
				
				jQuery(".providers_deleted span").text(response.deleted);
				if(response.deleted > 0 ){
					jQuery( ".providers_deleted" ).append( response.deleted_html );
				}
			}

			jQuery(".synch_result").show();
			jQuery(".sync_providers_btn").hide();
			jQuery(".close_synch_popup").show();
				
			componentHandler.upgradeAllRegistered();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", "#view_added_details", function(){	
	jQuery('#added_providers').show();
	jQuery(this).hide();
	jQuery('#hide_added_details').show();
});
jQuery(document).on("click", "#hide_added_details", function(){	
	jQuery('#added_providers').hide();
	jQuery(this).hide();
	jQuery('#view_added_details').show();
});

jQuery(document).on("click", "#view_updated_details", function(){	
	jQuery('#updated_providers').show();
	jQuery(this).hide();
	jQuery('#hide_updated_details').show();
});
jQuery(document).on("click", "#hide_updated_details", function(){	
	jQuery('#updated_providers').hide();
	jQuery(this).hide();
	jQuery('#view_updated_details').show();
});

jQuery(document).on("click", "#view_deleted_details", function(){	
	jQuery('#deleted_providers').show();
	jQuery(this).hide();
	jQuery('#hide_deleted_details').show();
});
jQuery(document).on("click", "#hide_deleted_details", function(){	
	jQuery('#deleted_providers').hide();
	jQuery(this).hide();
	jQuery('#view_deleted_details').show();
});

jQuery(document).on("change", "#wcast_enable_delivered_email", function(){	
	if(jQuery(this).prop("checked") == true){
		 jQuery('.delivered_shipment_label').addClass('delivered_enabel');
	     jQuery('.delivered_shipment_label .email_heading').addClass('disabled_link');
		 jQuery('.delivered_shipment_label .edit_customizer_a').addClass('disabled_link');
		 jQuery('.delivered_shipment_label .delivered_message').addClass('disable_delivered');
		 jQuery('#wcast_enable_delivered_status_email').prop('disabled', true);			 
    } else{
		 jQuery('.delivered_shipment_label').removeClass('delivered_enabel');
		 jQuery('.delivered_shipment_label .email_heading').removeClass('disabled_link');
		 jQuery('.delivered_shipment_label .edit_customizer_a').removeClass('disabled_link');
		 jQuery('.delivered_shipment_label .delivered_message').removeClass('disable_delivered');
		 jQuery('#wcast_enable_delivered_status_email').removeAttr('disabled');
	}
	componentHandler.upgradeAllRegistered();
});
jQuery(document).on("change", "#wc_ast_status_delivered", function(){	
	if(jQuery(this).prop("checked") == false){		
		jQuery('#wcast_enable_delivered_email')[0].checked = false;		
	}
	if(jQuery(this).prop("checked") == true && jQuery("#wcast_enable_delivered_email").prop("checked") == true){
		 jQuery('.delivered_shipment_label').addClass('delivered_enabel');
	     jQuery('.delivered_shipment_label .email_heading').addClass('disabled_link');
		 jQuery('.delivered_shipment_label .edit_customizer_a').addClass('disabled_link');
		 jQuery('.delivered_shipment_label .delivered_message').addClass('disable_delivered');
		 jQuery('#wcast_enable_delivered_status_email').prop('disabled', true);			 
    } else{
		 jQuery('.delivered_shipment_label').removeClass('delivered_enabel');
		 jQuery('.delivered_shipment_label .email_heading').removeClass('disabled_link');
		 jQuery('.delivered_shipment_label .edit_customizer_a').removeClass('disabled_link');
		 jQuery('.delivered_shipment_label .delivered_message').removeClass('disable_delivered');
		 jQuery('#wcast_enable_delivered_status_email').removeAttr('disabled');
	}
	componentHandler.upgradeAllRegistered();
});

jQuery(document).click(function(){
	var $trigger = jQuery(".dropdown");
    if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery(".dropdown-content").hide();
    }   
});
jQuery(document).on("click", ".dropdown_menu", function(){	
	jQuery('.dropdown-content').show();
});

function showerror(element){
	element.css("border","1px solid red");
}
function hideerror(element){
	element.css("border","1px solid #ddd");
}
jQuery(document).on("change", "#wc_ast_status_shipped", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery("[for=show_in_completed] .multiple_label").text('Shipped');
		jQuery("label .shipped_label").text('shipped');
	} else{
		jQuery("[for=show_in_completed] .multiple_label").text('Completed');
		jQuery("label .shipped_label").text('completed');
	}
});

jQuery(document).on("click", ".bulk_shipment_status_button", function(){
	jQuery("#content3").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var ajax_data = {
		action: 'bulk_shipment_status_from_settings',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',		
		success: function(response) {
			jQuery("#content3").unblock();
			jQuery( '.bulk_shipment_status_button' ).after( "<div class='bulk_shipment_status_success'>Tracking info sent to Trackship for all Orders.</div>" );
			jQuery( '.bulk_shipment_status_button' ).attr("disabled", true)
			//window.location.href = response;			
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".bulk_shipment_status_button_for_empty_balance", function(){
	jQuery("#content3").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var ajax_data = {
		action: 'bulk_shipment_status_for_empty_balance_from_settings',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',		
		success: function(response) {
			jQuery("#content3").unblock();
			jQuery( '.bulk_shipment_status_button_for_empty_balance' ).after( "<div class='bulk_shipment_status_success'>Tracking info sent to Trackship for all Orders.</div>" );
			jQuery( '.bulk_shipment_status_button_for_empty_balance' ).attr("disabled", true);
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".bulk_shipment_status_button_for_do_connection", function(){
	jQuery("#content3").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var ajax_data = {
		action: 'bulk_shipment_status_for_do_connection_from_settings',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',		
		success: function(response) {
			jQuery("#content3").unblock();
			jQuery( '.bulk_shipment_status_button_for_do_connection' ).after( "<div class='bulk_shipment_status_success'>Tracking info sent to Trackship for all Orders.</div>" );
			jQuery( '.bulk_shipment_status_button_for_do_connection' ).attr("disabled", true);
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".tab_input", function(){
	var tab = jQuery(this).data('tab');
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=woocommerce-advanced-shipment-tracking&tab="+tab;
	window.history.pushState({path:url},'',url);	
});

jQuery(document).on("click", ".open_video_popup", function(){
	jQuery('.how_to_video_popup').show();	 
});

jQuery(document).on("click", ".ts_addons_header", function(){
	jQuery('.ts_video_popup').show();	 
});

jQuery(document).on("click", ".how_to_video_popup .popupclose", function(){
	jQuery('#how_to_video').each(function(index) {
		jQuery(this).attr('src', jQuery(this).attr('src'));
		return false;
    });
});
jQuery(document).on("click", ".ts_video_popup .popupclose", function(){
	jQuery('#ts_video').each(function(index) {
		jQuery(this).attr('src', jQuery(this).attr('src'));
		return false;
    });
});