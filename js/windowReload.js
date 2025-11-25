(($) => {
  "use strict";

  $(() => {
    $("#wrapper").on("click", '[data-action="refresh"]', function (e) {
      e.preventDefault();
      window.location.reload();
    });
  });
})(jQuery);
