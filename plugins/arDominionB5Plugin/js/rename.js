(function ($) {
  var fields = ["title", "authorizedFormOfName", "slug", "filename"];
  var asyncOpCounter = 0;

  // Convert text (can be a title or slug text) to an available slug
  function fetchSlugPreview(title, callback) {
    // Assemble slug preview URL
    var urlParts = window.location.href.split("/");
    urlParts.pop();
    var slugPreviewUrl = urlParts.join("/") + "/slugPreview";

    $.ajax({
      url: slugPreviewUrl,
      data: { text: title },
      type: "GET",
      cache: false,
      success: function (results) {
        callback(false, results["slug"], results["padded"]);
      },
      error: function () {
        callback(true);
      },
    });
  }

  $(function () {
    var $renameForm = $("#rename-form");
    if (!$renameForm.length) {
      return;
    }

    // Place cursor in first field of form
    $("#rename-form input:text:visible:first").focus();

    // Create references to selectors
    var $renameFormSubmit = $("#rename-form-submit");
    var $slugExistsWarningAlert = $("#rename-slug-warning");

    var $fields = {};
    var $fieldCheckboxes = {};

    for (var index in fields) {
      var field = fields[index];
      $fields[field] = $("#" + field);
      $fieldCheckboxes[field] = $("#rename_enable_" + field);
    }

    // Cycle through fields and disable them if their corresponding checkbox isn't checked
    function enableFields() {
      for (var index in fields) {
        var field = fields[index];
        $fields[field].attr(
          "disabled",
          !$fieldCheckboxes[field].is(":checked")
        );
      }
    }

    // Update slug field by getting a slug preview based on the title
    function updateSlugUsingTitle() {
      // Only update slug preview if the slug field's enabled
      if ($fieldCheckboxes["slug"].is(":checked")) {
        fetchSlugPreview($fields["title"].val(), fetchSlugPreviewCallback);
      }
    }

    // Update slug field by getting a slug preview based on authorized form of name
    function updateSlugUsingAuthorizedFormOfName() {
      // Only update slug preview if the slug field's enabled
      if ($fieldCheckboxes["slug"].is(":checked")) {
        fetchSlugPreview(
          $fields["authorizedFormOfName"].val(),
          fetchSlugPreviewCallback
        );
      }
    }

    // Callback to handle slug preview results
    function fetchSlugPreviewCallback(err, slug, padded) {
      $slugExistsWarningAlert.hide();

      if (err) {
        alert("Error fetching slug preview.");
      } else {
        if (padded) {
          $slugExistsWarningAlert.show();
        }
        $fields["slug"].val(slug);
      }
    }

    // When no AJAX requests are pending, submit form data
    function trySubmit() {
      if (asyncOpCounter > 0) {
        setTimeout(trySubmit, 1000);
      } else {
        $renameForm.submit();
      }
    }

    // Enable/disable fields according to initial checkbox values
    enableFields();

    // Submit when users hits the enter key
    $renameForm.on("keydown", function (e) {
      if (e.which == 13) {
        e.preventDefault();

        // If user pressing enter from title field, update slug if enabled
        if ($fields["title"].is(":focus")) {
          updateSlugUsingTitle();
        }

        // If user pressing enter from authorized form of name field, update slug if enabled
        if ($fields["authorizedFormOfName"].is(":focus")) {
          updateSlugUsingAuthorizedFormOfName();
        }

        trySubmit();
      }
    });

    // Keep track of how many async requests are in progress
    $(document).ajaxStart(function () {
      asyncOpCounter++;
    });

    $(document).ajaxStop(function () {
      asyncOpCounter--;
    });

    // Enable/disable fields when checkboxes clicked
    $("#rename-form input[type=checkbox]").click(function (e) {
      enableFields();
    });

    // Simulate submit button
    $renameFormSubmit.click(function (e) {
      trySubmit();
    });

    // If title changes, update slug
    $fields["title"].change(function () {
      updateSlugUsingTitle();
    });

    // If authorized form of name changes, update slug
    $fields["authorizedFormOfName"].change(function () {
      updateSlugUsingAuthorizedFormOfName();
    });

    // If slug changes, sanitize it and indicate if it has already been used
    // by another resource
    $fields["slug"].change(function () {
      fetchSlugPreview($fields["slug"].val(), fetchSlugPreviewCallback);
    });
  });
})(window.jQuery);
