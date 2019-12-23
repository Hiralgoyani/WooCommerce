jQuery(document).ready(function(){
  jQuery('.slc-slider').slick({
  	infinite: true,
    slidesToShow: 4,
    slidesToScroll: 4,
     prevArrow: "<a href='#' class='slick-prevbtn'><</a>",
    nextArrow: "<a href='#' class='slick-nxtbtn'>></a>",

  // autoplay: true,
  // autoplaySpeed: 2000,
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

});