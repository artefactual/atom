(function ($) {

  var fields = ['title', 'slug', 'filename'];
  var asyncOpInProgress = false;

  // Fetch a slug preview for a given title
  function fetchSlugPreview(title, callback)
  {
    $.ajax({
      'url': window.location.href + '/slugPreview',
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
    // Create references to selectors
    var $renameModal = $('#renameModal');

    var $fields          = {};
    var $fieldCheckboxes = {};

    for (var index in fields) {
      var field = fields[index];
      $fields[field]          = $('#rename_' + field);
      $fieldCheckboxes[field] = $('#rename_enable_' + field);
    }

    $renameModalSubmit = $('#renameModalSubmit');
    $renameModalCancel = $('#renameModalCancel');

    // Cycle through fields and disable them if their corresponding checkbox isn't checked
    function enableFields() {
      for (var index in fields) {
        var field = fields[index];
        $fields[field].attr('disabled', !$fieldCheckboxes[field].is(':checked'));
      }
    }

    // Hide modal and submit form data
    function submit() {
      $renameModal.modal('hide');
      $("#renameModalForm").submit();
    }

    // When no AJAX requests are pending, hide modal and submit form data
    function trySubmit() {
      if (asyncOpInProgress) {
        setTimeout(trySubmit, 1000);
      } else {
        submit();
      }
    }

    // Enable/disable fields according to initial checkbox values
    enableFields();

    // Auto-focus on the first field
    $renameModal.on('shown', function () {
      $('input:text:visible:first', this).focus();
    });

    // Submit when users hits the enter key
    $renameModal.on('keypress', function (e) {
      if (e.keyCode == 13) {
        trySubmit();
      }
    });

    // Keep track of whether async requests are in progress
    $renameModal.ajaxStart(function() {
      asyncOpInProgress = true;
    });

    $renameModal.ajaxStop(function() {
      asyncOpInProgress = false;
    });

    // Add click handlers
    $renameModal.bind('show', function() {
      // Enable/disable fields when checkboxes clicked
      $('#renameModal form input[type=checkbox]').click(function(e) {
        enableFields();
      });

      // Simulate submit button
      $renameModalSubmit.click(function(e) {
        trySubmit();
      });

      // Hide form if cancel clicked
      $renameModalCancel.click(function(e) {
        $renameModal.modal('hide');
      });
    });

    // Remove click handlers when modal's hidden
    $renameModal.bind('hide', function() {
      $renameModalSubmit.unbind();
      $renameModalCancel.unbind();
      $('#renameModal form input[type=checkbox]').unbind();
    });

    // If title changes, update slug
    $fields['title'].change(function() {
      fetchSlugPreview($fields['title'].val(), function(err, slug) {
        if (err) {
          alert('Error fetching slug preview.');
        } else {
          $fields['slug'].val(slug);
        }
      });
    });
  });

})(window.jQuery);
