(function ($) {

  var fields = ['Title', 'Slug', 'Filename'];

  // Cycle through fields and disable them if their corresponding checkbox isn't checked
  function enableFields() {
    for (var index in fields) {
      var field = fields[index];
      $('#renameModal' + field).attr('disabled', !$('#renameModalEnable' + field).is(':checked'));
    }
  }

  // Post updates to fields whose corresponding checkboxes are checked
  function postUpdates(callback) {
    var updatedFields = {};

    for (var index in fields) {
      var field = fields[index];
      if ($('#renameModalEnable' + field).is(':checked')) {
        updatedFields[field.toLowerCase()] = $('#renameModal' + field).val();
      }
    }

  /*
  $.ajax({
    'url': '/transfer/cleanup_metadata_set/' + uuid + '/',
    'type': 'POST',
    'async': false,
    'cache': false,
    'data': updatedFields,
    'success': function() {
      callback();
    },
    'error': function() {
      callback(true, 'Error sending update');
    }
  });
  */

    console.log(updatedFields);
  }

  $(function() {
    enableFields();

    // Add click handlers
    $('#renameModal').bind('show', function() {
      $('#renameModalSubmit').click(function(e) {
        postUpdates();

        $('#renameModal').modal('hide');
      });

      $('#renameModalCancel').click(function(e) {
        $('#renameModal').modal('hide');
      });

      $('#renameModal form input[type=checkbox]').click(function(e) {
        enableFields();
      });
    });

    // Remove click handlers
    $('#renameModal').bind('hide', function() {
      $('#renameModalSubmit').unbind();
      $('#renameModalCancel').unbind();
      $('#renameModal form input[type=checkbox]').unbind();
    });
  });

})(window.jQuery);
