(($) => {
  "use strict";

  $(() => {
    $("[id^=acl-modal-container-]").each((_, element) => {
      var $modal = $(element);
      var $triggerButton = $("#" + $modal.data("trigger-button"));
      var $hiddenInput = $modal.find("input[type=hidden]:first()");
      var $autocomplete = $modal.find(".form-autocomplete");
      var $submitButton = $modal.find(".btn-success");
      var b5Modal = bootstrap.Modal.getOrCreateInstance($modal);

      // Focus autocomplete on modal shown
      $modal.on("shown.bs.modal", () => $autocomplete.trigger("focus"));

      // Add new permissions table on submit
      $submitButton.on("click", () => {
        var objectId = $hiddenInput.val().trim().split("/").pop();
        if (objectId) {
          // Don't duplicate existing tables
          var $table = $("table#acl_" + objectId);
          if ($table.length) {
            // Highlight caption of duplicate table for two seconds
            var classes = "p-3 mb-2 rounded mark fw-bold";
            var $caption = $table.find("caption span");
            $caption.addClass(classes);
            setTimeout(() => $caption.removeClass(classes), 2000);
          } else {
            // Clone hidden table
            var $newTable = $modal.next(".acl-table-container").clone();
            // Add autocomplete value as caption
            $newTable.find("caption span").text($autocomplete.val());
            // Add table id
            $newTable.find("table").prop("id", "acl_" + objectId);
            // Update object id in input and label attributes
            $newTable.find("input").each((_, input) => {
              var $input = $(input);
              $input.prop(
                "id",
                $input.prop("id").replace("{objectId}", objectId)
              );
              $input.prop(
                "name",
                $input.prop("name").replace("{objectId}", objectId)
              );
            });
            $newTable.find("label").each((_, label) => {
              var $label = $(label);
              $label.prop(
                "for",
                $label.prop("for").replace("{objectId}", objectId)
              );
            });
            // Append before the trigger button and make it visible
            $newTable.insertBefore($triggerButton).removeClass("d-none");
          }
        }

        // Hide modal and clear autocomplete
        b5Modal.hide();
        $hiddenInput.val("");
        $autocomplete.val("");
      });
    });
  });
})(jQuery);
