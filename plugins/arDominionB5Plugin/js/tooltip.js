($ => {

  'use strict';

  $(() => {
    $('[data-tooltip]').each((_, element) => {
      new bootstrap.Tooltip(element, {
        title: $(element).data('tooltip'),
        trigger: 'hover',
        customClass: 'd-none d-lg-block',
        placement: 'bottom',
      })
    });
  });

})(jQuery);
