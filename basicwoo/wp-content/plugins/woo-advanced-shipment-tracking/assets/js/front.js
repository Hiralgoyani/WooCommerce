jQuery(document).on("submit", ".order_track_form", function(){
	var form = jQuery(this);
	var error;
	jQuery(".order_track_form ").block({
    message: null,
    overlayCSS: {
        background: "#fff",
        opacity: .6
	}	
    });
	jQuery.ajax({
		url: zorem_ajax_object.ajax_url,		
		data: form.serialize(),
		type: 'POST',
		success: function(response) {
			if(response == 'tracking_items_not_found'){				
				jQuery(".track_fail_msg ").show();
				jQuery(".track_fail_msg ").text('Tracking details not found.');
			} else if(response){
				jQuery('.track-order-section').replaceWith(response);
			} else{				
				jQuery(".track_fail_msg ").show();
				jQuery(".track_fail_msg ").text('Order id not found.');
			}
			jQuery(".order_track_form ").unblock();	
		},
		error: function(jqXHR, exception) {
			console.log(jqXHR.status);
			if(jqXHR.status == 302){				
				jQuery(".track_fail_msg ").show();
				jQuery(".track_fail_msg ").text('Tracking details not found.');
				jQuery(".order_track_form ").unblock();	
			} else{				
				jQuery(".track_fail_msg ").show();
				jQuery(".track_fail_msg ").text('There are some issue with Trackship.');
				jQuery(".order_track_form ").unblock();	
			}	
			
		}
	});
	return false;
});
jQuery(document).on("click", ".back_to_tracking_form", function(){
	jQuery('.tracking-detail').hide();
	jQuery('.track-order-section').show();
});
jQuery(document).on("click", ".view_table_rows", function(){
	jQuery(this).hide();
	jQuery(this).closest('.shipment_progress_div').find('.hide_table_rows').show();
	jQuery(this).closest('.shipment_progress_div').find('table.tracking-table tr:nth-child(n+3)').show();	
});
jQuery(document).on("click", ".hide_table_rows", function(){
	jQuery(this).hide();
	jQuery(this).closest('.shipment_progress_div').find('.view_table_rows').show();
	jQuery(this).closest('.shipment_progress_div').find('table.tracking-table tr:nth-child(n+3)').hide();	
});