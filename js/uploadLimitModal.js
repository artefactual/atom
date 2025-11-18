(($) => {
  "use strict";

  $(() => {
    const $modal = $("#upload-limit-modal");

    if (!$modal.length) {
      return;
    }

    const $form = $("#upload-limit-form", $modal);
    const $submitButton = $(".btn-success", $modal);
    const $limitNumberInput = $("input#uploadLimit_value", $modal);
    const b5Modal = bootstrap.Modal.getOrCreateInstance($modal);

    $submitButton.on("click", () => {
      $form.trigger("submit");
    });

    $form.on("submit", (event) => {
      event.preventDefault();

      // Serialize and submit the form asynchronously.
      $.ajax({
        url: $form.attr("action"),
        type: $form.attr("method"),
        data: $form.serialize(),
      })
        .done(handleSuccess)
        .fail(handleFail)
        .always(() => {
          b5Modal.hide();
        });
    });

    // Replace widget using the new version delivered by the server.
    const handleSuccess = (response) => {
      $("#upload-limit-card").replaceWith(response);

      const $alert = $("#upload-limit-card .alert-success");
      showAlert($alert);
    };

    const handleFail = () => {
      const $alert = $("#upload-limit-card .alert-danger");
      showAlert($alert);
    };

    const showAlert = ($alert) => {
      $alert
        .removeClass("d-none")
        .attr("aria-hidden", false)
        .delay(2500)
        .queue(function (next) {
          const $this = $(this);
          $this.slideUp(250, function () {
            $this.addClass("d-none").attr("aria-hidden", true);
          });
          next();
        });
    };

    // Focus on text box when radio is clicked for a quicker update.
    const elemsSelector =
      "#uploadLimit_type_limited, label[for=uploadLimit_type_limited]";
    $(elemsSelector, $form).on("click", (event) => {
      $limitNumberInput.trigger("focus");
    });
  });
})(jQuery);
