(($) => {
  "use strict";

  $(() => {
    const refreshJobsButton = $("#jobs-refresh-button");

    refreshJobsButton.on("click", (e) => {
      e.preventDefault(); // Prevent the default behavior of the anchor element
      window.location.reload();
    });
  });
})(jQuery);
