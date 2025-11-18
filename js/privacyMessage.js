(($) => {
  "use strict";

  $(() => {
    $("#privacy-message").on("closed.bs.alert", () => {
      $.get("/default/privacyMessageDismiss");
      $(".navbar-brand").trigger("focus");
    });
  });
})(jQuery);
