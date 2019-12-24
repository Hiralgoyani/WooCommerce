jQuery(document).ready(function(){
  jQuery('.slc-slider').slick({
  	infinite: true,
    slidesToShow: 4,
    slidesToScroll: 4,
     prevArrow: "<a href='#' class='slick-prevbtn'><</a>",
    nextArrow: "<a href='#' class='slick-nxtbtn'>></a>",
  });


    //code to add validation on "Add to Cart" button
    jQuery('.single_add_to_cart_button').click(function(){
        var custom_data_1 = jQuery('#custom_data_1').val();
        var custom_data_2 = jQuery('#custom_data_2').val();
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        jQuery.ajax({
            url: my_ajax_object.ajax_url, //AJAX file path - admin_url('admin-ajax.php')
            type: "POST",
            data: {
                action:'wdm_add_user_custom_data_options',
                custom_data_1 : custom_data_1,
                custom_data_2 : custom_data_2
            },
            async : false,
            success: function(data){
            }
        });
    });


    // add to cart using ajax(without plugin)
    (function ($) {
	    $(document).on('click', '.single_add_to_cart_button', function (e) {
	        e.preventDefault();

	        var $thisbutton = $(this),
	                $form = $thisbutton.closest('form.cart'),
	                id = $thisbutton.val(),
	                product_qty = $form.find('input[name=quantity]').val() || 1,
	                product_id = $form.find('input[name=product_id]').val() || id,
	                variation_id = $form.find('input[name=variation_id]').val() || 0;
	                console.log($form);

	        var data = {
	            action: 'woocommerce_ajax_add_to_cart',
	            product_id: product_id,
	            product_sku: '',
	            quantity: product_qty,
	            variation_id: variation_id,
	        };

	        $(document.body).trigger('adding_to_cart', [$thisbutton, data]);

	        $.ajax({
	            type: 'post',
	            url: wc_add_to_cart_params.ajax_url,
	            data: data,
	            beforeSend: function (response) {
	                $thisbutton.removeClass('added').addClass('loading');
	            },
	            complete: function (response) {
	                $thisbutton.addClass('added').removeClass('loading');
	            },
	            success: function (response) {

	                if (response.error & response.product_url) {
	                    window.location = response.product_url;
	                    return;
	                } else {
	                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
	                }
	            },
	        });

	        return false;
	    });
	})(jQuery);

});