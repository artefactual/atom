(($) => {
  "use strict";

  $(() => {
    var node = $("#unog-carousel");

    $(node)
      .imagesLoaded()
      .always(() => {
        $("#unog-slider-images").slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          draggable: false,
          swipe: false,
          arrows: false,
          dots: false,
        });
        $("#unog-slider-title").slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          asNavFor: "#unog-slider-images",
          draggable: false,
          swipe: false,
          arrows: true,
          dots: true,
          fade: true,
          arrowsPlacement: 'beforeSlides',
          autoplay: true,
          autoplaySpeed: 5000,
        });
        $("#unog-slider-images").slick("slickGoTo", 0);
      });
  });
})(jQuery);
