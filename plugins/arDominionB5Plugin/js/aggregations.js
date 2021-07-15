($ => {

  'use strict';

  $(() => {
    // Open the first three aggregations
    $('.aggregation .collapse').each((index, item) => {
      var collapse = bootstrap.Collapse.getOrCreateInstance(
        item, {toggle: false}
      );
      if (index < 3) collapse.show();
    });
  });
  
})(jQuery);
