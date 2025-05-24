(function ($) {
  function showOrRemoveIdentifierError($identifierEl, allowable, message) {
    // Remove any currently displayed identifier errors
    $('.identifier-error').remove();

    // Show error if identifier not available
    if (!allowable)
    {
      $messageHtml = '<div class="identifier-error alert alert-danger">' + message + '</div>';
      $identifierEl.after($messageHtml);
    }
  }

  $(function() {
    var formValid = false,
        $identifierEl = $('#identifier'),
        $spinnerEl = $('#spinner'),
        $identifierCheckServerErrorEl = $('#identifier-check-server-error'),
        identifierAvailableCheckUrl = $('#identifierAvailableCheckUrl').val();

    // If identifier is already populated, then it has been validated by
    // server-side logic
    if ($identifierEl.val()) {
      formValid = true;
    }

    // Disable form submission if form isn't valid or there's no identifier
    $('#editForm').submit(function(e){
      if (!formValid || !$identifierEl.val())
      {
        e.preventDefault();
      }
    });

    // Activate identifier pre-validation
    $identifierEl.change(function() {
      var searchParams = {'identifier': $identifierEl.val()};

      $.ajax({
        url: URI(identifierAvailableCheckUrl).addSearch(searchParams).toString(),

        beforeSend: function() {
          $spinnerEl.show();
        },

        success: function(data) {
          $identifierCheckServerErrorEl.hide();
          formValid = data['allowable'];
          showOrRemoveIdentifierError($identifierEl, data['allowable'], data['message']);
        },

        complete: function () {
          $spinnerEl.hide();
        },

        error: function (jqXHR, textStatus, thrownError) {
          $identifierCheckServerErrorEl.show();
        }
      });
    });
  });
})(window.jQuery);  
