($ => {

  'use strict';

  $(() => {
    $('.navbar [data-tooltip]').each((_, element) => {
      new bootstrap.Tooltip(element, {
        title: $(element).data('tooltip'),
        customClass: 'd-none d-lg-block',
        placement: 'bottom',
      });
    });
  });

})(jQuery);
