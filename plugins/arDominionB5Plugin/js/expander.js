(($) => {
  "use strict";

  Drupal.behaviors.expander = {
    attach: () => {
      // Get i18n text for read more/less links from footer
      var $i18n = $("#js-i18n #read-more-less-links");
      $(".search-result .text-block, div.field:not(:has(div.field)) > div")
        .expander({
          slicePoint: 255,
          expandText: $i18n.data("read-more-text"),
          userCollapseText: $i18n.data("read-less-text"),
        })
        .removeClass("d-none");
    },
  };
})(jQuery);
