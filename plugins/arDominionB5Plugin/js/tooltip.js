import Tooltip from "bootstrap/js/dist/tooltip";

(($) => {
  "use strict";

  $(() => {
    $("body.edit.show-edit-tooltips [id$=-help]").each((_, el) => {
      $(el).prevAll(":not([type=hidden]):first").attr({
        title: el.textContent.trim(),
        "data-bs-toggle": "tooltip",
        "data-bs-trigger": "focus",
        "data-bs-placement": "left",
      });
    });

    $("[data-bs-toggle=tooltip]").each((_, el) => new Tooltip(el));
  });
})(jQuery);
