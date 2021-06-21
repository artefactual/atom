(function($) {

  'use strict';

  $(function() {
    $('#privacy-message').on('closed.bs.alert', function() {
      $.get('/default/privacyMessageDismiss');
      $('.navbar-brand').trigger('focus');
    });
  });

})(jQuery);
