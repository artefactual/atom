(($) => {
  "use strict";

  const $container = $(".masonry");
  if (!$container.length) {
    return;
  }

  // Rely on imagesloaded to init Masonry once all images have loaded.
  // DOMContentLoaded triggers too early, window.onload triggers too late.
  $container.imagesLoaded().always(() => {
    $container.masonry({
      itemSelector: ".masonry-item",
      percentPosition: true,
    });
  });
})(jQuery);
