function QubitAclDialog(dialogId, tableTemplate, $)
{
  thisDialog = this;
  this.tableTemplate = tableTemplate;
  this.label = $('a#'+dialogId+'Link').text();

  // Create YUI container for dialog
  this.wrapper = $('<div class="yui-skin-sam">' +
    '<div id="'+dialogId+'Wrapper">' +
      '<div class="hd">' + this.label + '</div>' +
      '<div class="bd">' +
        '<form action="" method="post" style="border: none"></form>' +
      '</div>' +
    '</div>' +
  '</div>');

  $('#'+dialogId).remove().appendTo(this.wrapper.find('form'));
  $('#editForm').before(this.wrapper);

  // YUI dialog config
  this.handleCancel = function() {
    this.cancel();
  };

  this.handleSubmit = function() {
    var input = $('[id="'+dialogId+'"]').find('input[name]').eq(0);

    // Don't duplicate an existing table
    if ('null' != input.val())
    {
      var objectId = input.val();
      var tableId = $(tableTemplate).find('table').attr('id').replace('{objectId}', objectId);

      if (0 < $('[id="'+tableId+'"]').length)
      {
        // Highlight caption of duplicate table
        var caption = $('[id="'+tableId+'"] caption');
        caption.css('background', 'yellow');

        setTimeout(function () {
          caption.css('background', 'none');
        }, 1000);
      }
      else
      {
        var newTable = tableTemplate;

        // Search and replace '{objectId}'
        while (0 <= newTable.indexOf('{objectId}'))
        {
          newTable = newTable.replace('{objectId}', objectId);
        }

        newTable = $(newTable);
        newTable.find('caption').text(input.next('input').val());
        newTable.hide();
        jQuery('a#'+dialogId+'Link').parent('div').before(newTable);
        newTable.slideDown();
      }
    }

    // Hide dialog
    this.hide();

    // Clear dialog values
    input.val('null');
    input.next('input').val(null);
  };

  this.config = {
    width: "480px",
    zIndex: 20000,
    fixedcenter: true,
    draggable: true,
    visible: false,
    modal: true,
    constraintoviewport: true,
    postmethod: "none" }

  this.buttons = [
    { text: "Submit", handler: this.handleSubmit, isDefault: true },
    { text: "Cancel", handler: this.handleCancel }
  ];

  // Render dialog
  this.yuiDialog = new YAHOO.widget.Dialog(dialogId+"Wrapper", this.config);
  this.yuiDialog.cfg.queueProperty("buttons", this.buttons);
  this.yuiDialog.render();

  // Remove all showEvent listeners to prevent default 'focusFirst' behaviour
  this.yuiDialog.showEvent.unsubscribeAll();

  // Focus on first visible input field
  this.yuiDialog.showEvent.subscribe(function () {
    $('[id="'+dialogId+'"]').find('input:visible').get(0).focus();
  }, this, true);

  // Wrap YUI dialog show method
  this.show = function()
  {
    this.yuiDialog.show();
  }
}
