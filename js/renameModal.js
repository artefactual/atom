(function ($) {

  var fields = ['Title', 'Slug', 'Filename'];
  var asyncOpInProgress = false;

  // Cycle through fields and disable them if their corresponding checkbox isn't checked
  function enableFields() {
    for (var index in fields) {
      var field = fields[index];
      $('#renameModal' + field).attr('disabled', !$('#renameModalEnable' + field).is(':checked'));
    }
  }

  function fetchSlugPreview(title, callback)
  {
    $.ajax({
      'url': window.location.href + '/slugPreview',
      'data': {'title': title},
      'type': 'GET',
      'cache': false,
      'success': function(results) {
        asyncOpInProgress = false;
        callback(false, results['slug']);
      },
      'error': function() {
        callback(true);
      }
    });
  }

  // Hide modal and submit form data when no AJAX requests pending
  function trySubmit() {
    // Wait until AJAX requests have completed
    if (asyncOpInProgress) {
      setTimeout(trySubmit, 1000);
    } else {
      submit();
      setSlugPreview();
    }
  }

  function submit() {
    // Hide modal and submit data
    $('#renameModal').modal('hide');
    $("#renameModalForm").submit();
  }

  function setSlugPreview()
  {
    fetchSlugPreview($('#renameModalTitle').val(), function(err, slug) {
      if (err) {
        alert('Error fetching slug preview.');
      } else {
        $('#renameModalSlug').val(slug);
      }
    });
  }

  $(function() {
    // Enable/disable fields according to checkbox values
    enableFields();

    // Keep track of whether async requests are in progress
    $('#renameModal').ajaxStart(function() {
      asyncOpInProgress = true;
    });

    $('#renameModal').ajaxStop(function() {
      asyncOpInProgress = false;
    });

    // If title changes, update slug
    $('#renameModalTitle').change(function() {
      setSlugPreview();
    });

    // Add click handlers
    $('#renameModal').bind('show', function() {
      // Enable/disable fields when checkboxes clicked
      $('#renameModal form input[type=checkbox]').click(function(e) {
        enableFields();
      });

      // Simulate submit button
      $('#renameModalSubmit').click(function(e) {
        trySubmit();
      });

      // Hide form if cancel clicked
      $('#renameModalCancel').click(function(e) {
        $('#renameModal').modal('hide');
      });
    });

    // Remove click handlers when modal's hidden
    $('#renameModal').bind('hide', function() {
      $('#renameModalSubmit').unbind();
      $('#renameModalCancel').unbind();
      $('#renameModal form input[type=checkbox]').unbind();
    });

    // Auto-focus on the first field
    $('#renameModal').on('shown', function () {
      $('input:text:visible:first', this).focus();
    });

    // Submit when users hits the enter key
    $('#renameModal').on('keypress', function (e) {
      if (e.keyCode == 13) {
        trySubmit();
      }
    });
  });

})(window.jQuery);
