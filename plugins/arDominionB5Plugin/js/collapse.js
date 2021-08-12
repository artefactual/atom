(($) => {
  "use strict";

  $(() => {
    if ($("#editForm .accordion-item").length) {
      var $collapseToShow = $(location.hash);
      if ($collapseToShow.length) {
        $collapseToShow.on("shown.bs.collapse", (e) => {
          window.scrollTo(0, $(e.target).parent().offset().top);
        });
        bootstrap.Collapse.getOrCreateInstance($collapseToShow);
      }
    }
  });
})(jQuery);
