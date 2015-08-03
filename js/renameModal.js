(function ($) {

  var fields = ['Title', 'Slug', 'Filename'];

  // Cycle through fields and disable them if their corresponding checkbox isn't checked
  function enableFields() {
    for (var index in fields) {
      var field = fields[index];
      $('#renameModal' + field).attr('disabled', !$('#renameModalEnable' + field).is(':checked'));
    }
  }

  // Hide modal and submit form data
  function submit() {
    $('#renameModal').modal('hide');
    $("#renameModalForm").submit();
  }

  $(function() {
    // Enable/disable fields according to checkbox values
    enableFields();

    // Add click handlers
    $('#renameModal').bind('show', function() {
      // Enable/disable fields when checkboxes clicked
      $('#renameModal form input[type=checkbox]').click(function(e) {
        enableFields();
      });

      // Simulate submit button
      $('#renameModalSubmit').click(function(e) {
        submit();
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
        submit();
      }
    });
  });

})(window.jQuery);
