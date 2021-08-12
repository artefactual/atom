(($) => {
  "use strict";

  $(() => {
    // Data attributes trigger doesn't work properly
    $(".masonry").masonry({
      itemSelector: ".masonry-item",
      percentPosition: true,
    });
  });
})(jQuery);
