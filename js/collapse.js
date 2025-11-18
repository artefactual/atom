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

    $("form .accordion-item").each(function () {
      // Check for error elements and force accordion open
      $(this)
        .find("[id$='-errors']")
        .each(function () {
          const $accordionCollapse = $(this).closest(".accordion-collapse");
          if ($accordionCollapse.length) {
            $accordionCollapse.addClass("show");
          }
        });
    });
  });
})(jQuery);
