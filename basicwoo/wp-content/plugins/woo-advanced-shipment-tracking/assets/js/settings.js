jQuery(document).on("click", ".shipping_provider_tab li", function(){
	jQuery(".shipping_provider_tab li").removeClass("current");
	jQuery(this).addClass("current");
		
	var target = jQuery(this).data("target");
	
	jQuery(".targets").hide();
	jQuery(".target-"+target).show();
});
jQuery(document).on("click", "#wc_ast_status_delivered", function(){
	if(jQuery(this).prop("checked") == true){
        jQuery('.status_label_color_th').show();
		jQuery('label.tab_label[for="tab5"]').show();
    } else{
		jQuery('.status_label_color_th').hide();
		jQuery('label.tab_label[for="tab5"]').hide();
	}
	var email_type = jQuery('#wc_ast_select_email_type').val();
	if(email_type == 'wc_email' && jQuery(this).prop("checked") == true){				
		jQuery('.manage_delivered_order_email_link').show();		
	} else{			
		jQuery('.manage_delivered_order_email_link').hide();
	}	
});

jQuery(document).on("click", "#wc_ast_status_partial_shipped", function(){
	
	if(jQuery(this).prop("checked") == true){
        jQuery('.partial_shipped_status_label_color_th').show();
		jQuery('#partial-shipped').closest( ".mdl-list__item-secondary-action" ).show();
    } else{
		jQuery('.partial_shipped_status_label_color_th').hide();		
		jQuery('#partial-shipped').closest( ".mdl-list__item-secondary-action" ).hide();
	}

});

jQuery(document).on("change", "#wc_ast_select_email_type", function(){
	jQuery("#content2 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var email_type = jQuery(this).val();
	var ajax_data = {
		action: 'update_email_type',
		email_type: email_type,	
	};	
	jQuery.ajax({
		url: ajaxurl,
		data:ajax_data,
		type: 'POST',
		success: function(response) {
			if(email_type == 'wc_email'){		
				jQuery('label.tab_label[for="tab5"]').hide();
				jQuery('.manage_delivered_order_email_link').show();
			} else{	
				jQuery('label.tab_label[for="tab5"]').show();
				jQuery('.manage_delivered_order_email_link').hide();
			}	
			jQuery("#content2 ").unblock();			
			var snackbarContainer = document.querySelector('#demo-toast-example');
			var data = {message: 'Data updated successfully.'};
			snackbarContainer.MaterialSnackbar.showSnackbar(data);
		},
		error: function(response) {
			console.log(response);			
		}
	});	
});
jQuery( document ).ready(function() {	
	jQuery(".woocommerce-help-tip").tipTip();
	
	if(jQuery('#wc_ast_status_delivered').prop("checked") == true){
		jQuery('.status_label_color_th').show();		
	} else{
		jQuery('.status_label_color_th').hide();		
	}

	if(jQuery('#wc_ast_status_partial_shipped').prop("checked") == true){
		jQuery('.partial_shipped_status_label_color_th').show();		
	} else{
		jQuery('.partial_shipped_status_label_color_th').hide();			
	}	
	
	jQuery('#wc_ast_status_label_color').wpColorPicker();
	jQuery('#wc_ast_status_partial_shipped_label_color').wpColorPicker();
	jQuery('.color_field input').wpColorPicker();		
});
jQuery(document).on("click", '#variable_tag #var_input', function(e){
	jQuery(this).focus();
	jQuery(this).select();
	jQuery(this).next('.copy').show().delay(1000).fadeOut();	
	document.execCommand('copy');	
});