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

});