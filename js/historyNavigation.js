(($) => {
  "use strict";

  $(() => {
    historyNavigation();
  });

  function historyNavigation() {
    document.querySelectorAll('[data-action="back"]').forEach(function (el) {
      el.addEventListener("click", function (e) {
        e.preventDefault();

        if (window.history.length > 1) {
          window.history.back();
        } else {
          const fallbackUrl = el.getAttribute("data-fallback-url");
          if (fallbackUrl) {
            window.location.href = fallbackUrl;
            return;
          }
        }
      });
    });
  }
})(jQuery);
