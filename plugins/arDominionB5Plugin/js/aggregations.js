(($) => {
  "use strict";

  $(() => {
    var $aggs = $("#collapse-aggregations");
    if ($aggs.length) {
      // Use a div with Bootstrap classes to check the current
      // breakpoint based on its visibility, expand the main
      // aggregations section on medium screens and open the
      // first three aggregations.
      var $div = $('<div class="d-none d-md-block">');
      if ($div.appendTo($("body")).is(":visible")) {
        bootstrap.Collapse.getOrCreateInstance($aggs);
        $(".aggregation .collapse").each((index, item) => {
          var agg = bootstrap.Collapse.getOrCreateInstance(item, {
            toggle: false,
          });
          if (index < 3) agg.show();
        });
      }
      $div.remove();
    }
  });
})(jQuery);
