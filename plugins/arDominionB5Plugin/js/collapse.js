($ => {

  'use strict';

  $(() => {
    if ($('#editForm .accordion-item').length) {
      var $collapseToShow = $(location.hash);
      if ($collapseToShow.length) {
        $collapseToShow.on('shown.bs.collapse', function (e) {
          window.scrollTo(0, $(e.target).parent().offset().top);
        });
        bootstrap.Collapse.getOrCreateInstance($collapseToShow.get(0)).show();
      }
    }
  });

})(jQuery);
