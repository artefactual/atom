(($) => {
  "use strict";
  Drupal.behaviors.imageflow = {
    attach: () => {
      var node = $("#atom-digital-object-carousel");

      if (!$(node).length) {
        return;
      }

      $(node)
        .imagesLoaded()
        .always(() => {
          $("#atom-slider-images").slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            asNavFor: "#atom-slider-title",
            dots: true,
            centerMode: true,
            instructionsText: $(node).data(
              "carousel-instructions-text-image-link"
            ),
            regionLabel: $(node).data("carousel-images-region-label"),
            variableWidth: true,
            centerPadding: "60px",
            nextArrow:
              '<button class="slick-next slick-arrow" type="button" style=""><span class="slick-next-icon" aria-hidden="true"></span><span class="slick-sr-only">' +
              $(node).data("carousel-next-arrow-button-text") +
              "</span></button>",
            prevArrow:
              '<button class="slick-prev slick-arrow" type="button" style=""><span class="slick-prev-icon" aria-hidden="true"></span><span class="slick-sr-only">' +
              $(node).data("carousel-prev-arrow-button-text") +
              "</span></button>",
          });

          $("#atom-slider-title").slick({
            centerMode: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            draggable: false,
            swipe: false,
            arrows: false,
            fade: true,
            instructionsText: $(node).data(
              "carousel-instructions-text-text-link"
            ),
            regionLabel: $(node).data("carousel-title-region-label"),
          });

          $("#atom-slider-images").slick("slickGoTo", 0);
        });
    },
  };
})(jQuery);
