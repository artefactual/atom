(function ($) {

  var fields = ['title', 'slug', 'filename'];
  var asyncOpInProgress = false;

  // Fetch a slug preview for a given title
  function fetchSlugPreview(title, callback)
  {
    // Assemble slug preview URL
    var urlParts = window.location.href.split('/');
    urlParts.pop();
    var slugPreviewUrl = urlParts.join('/') + '/slugPreview';

    $.ajax({
      'url': slugPreviewUrl,
      'data': {'title': title},
      'type': 'GET',
      'cache': false,
      'success': function(results) {
        callback(false, results['slug']);
      },
      'error': function() {
        callback(true);
      }
    });
  }

  $(function() {

    // Place cursor in first field of form
    $('#rename-form input:text:visible:first').focus();

    // Create references to selectors
    var $renameForm = $('#rename-form');

    var $fields          = {};
    var $fieldCheckboxes = {};

    for (var index in fields) {
      var field = fields[index];
      $fields[field]          = $('#' + field);
      $fieldCheckboxes[field] = $('#rename_enable_' + field);
    }

    $renameFormSubmit = $('#rename-form-submit');

    // Cycle through fields and disable them if their corresponding checkbox isn't checked
    function enableFields() {
      for (var index in fields) {
        var field = fields[index];
        $fields[field].attr('disabled', !$fieldCheckboxes[field].is(':checked'));
      }
    }

    function updateSlugPreview() {
      fetchSlugPreview($fields['title'].val(), function(err, slug) {
        if (err) {
          alert('Error fetching slug preview.');
        } else {
          $fields['slug'].val(slug);
        }
      });
    }

    // When no AJAX requests are pending, submit form data
    function trySubmit() {
      if (asyncOpInProgress) {
        setTimeout(trySubmit, 1000);
      } else {
        $renameForm.submit();
      }
    }

    // Enable/disable fields according to initial checkbox values
    enableFields();

    // Submit when users hits the enter key
    $renameForm.on('keypress', function (e) {
      if (e.keyCode == 13) {
        e.preventDefault();
        updateSlugPreview();
        trySubmit();
      }
    });

    // Keep track of whether async requests are in progress
    $renameForm.ajaxStart(function() {
      asyncOpInProgress = true;
    });

    $renameForm.ajaxStop(function() {
      asyncOpInProgress = false;
    });

    // Enable/disable fields when checkboxes clicked
    $('#rename-form input[type=checkbox]').click(function(e) {
      enableFields();
    });

    // Simulate submit button
    $renameFormSubmit.click(function(e) {
      trySubmit();
    });

    // If title changes, update slug
    $fields['title'].change(function() {
      updateSlugPreview();
    });
  });

})(window.jQuery);
