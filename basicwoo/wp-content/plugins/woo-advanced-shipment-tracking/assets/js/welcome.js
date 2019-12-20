jQuery(document).on("click", ".ast-save-setup-settings", function(){
	jQuery(".ast-onboarding-step-welcome").hide();
	jQuery(".ast-onboarding-step-shipping").show();
	jQuery(".ast-onboarding-step-delivered").hide();
	jQuery(".ast-onboarding-step-trackship").hide();
	jQuery(".ast-onboarding-wizard-step").removeClass('ast-onboarding-wizard-step-active');
	jQuery(".step-welcome").addClass('ast-onboarding-wizard-step-completed');
	jQuery(".step-shipping").addClass('ast-onboarding-wizard-step-active');	
	
	var $wc_ast_settings_form = jQuery('#wc_ast_settings_form');
	var ajax_data = $wc_ast_settings_form.serialize();
			
	jQuery.post( ajaxurl, ajax_data, function(response) {		
	});
});

jQuery(document).on("click", ".ast-save-setup-providers", function(){
	jQuery(".ast-onboarding-step-welcome").hide();
	jQuery(".ast-onboarding-step-shipping").hide();
	jQuery(".ast-onboarding-step-delivered").show();
	jQuery(".ast-onboarding-step-trackship").hide();
	jQuery(".ast-onboarding-wizard-step").removeClass('ast-onboarding-wizard-step-active');
	jQuery(".step-shipping").addClass('ast-onboarding-wizard-step-completed');
	jQuery(".step-delivered").addClass('ast-onboarding-wizard-step-active');	
});

jQuery(document).on("click", ".ast-save-setup-delivered", function(){
	jQuery(".ast-onboarding-step-welcome").hide();
	jQuery(".ast-onboarding-step-shipping").hide();
	jQuery(".ast-onboarding-step-delivered").hide();
	jQuery(".ast-onboarding-step-trackship").show();
	jQuery(".ast-onboarding-wizard-step").removeClass('ast-onboarding-wizard-step-active');
	jQuery(".step-delivered").addClass('ast-onboarding-wizard-step-completed');
	jQuery(".step-trackship").addClass('ast-onboarding-wizard-step-active');	
	
	var $wc_ast_settings_form = jQuery('#wc_ast_delivered_settings_form');
	var ajax_data = $wc_ast_settings_form.serialize();
			
	jQuery.post( ajaxurl, ajax_data, function(response) {		
	});
});