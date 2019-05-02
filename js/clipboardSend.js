"use strict";

(function ($) {
  $(document).ready(function()
    {
      // Hide submit form button (JavaScript's working so it's not needed)
      $('#sendFormSubmit').hide();

      // Automatically submit form
      $('#sendForm').submit();
    });
})(jQuery);
