(function ($) {

  "use strict";

  var PrivacyMessage = function (element)
  {
    this.$element = $(element);
    this.$privacyMessageBlock = this.$element.find('div[id="privacy-message"]');
    this.$privacyMessageButton = this.$element.find('button.privacy-message-button');
    this.listen();
  };

  PrivacyMessage.prototype = {

    constructor: PrivacyMessage,

    listen: function()
    {
      this.$privacyMessageButton.on('click', $.proxy(this.onPrivacyMessageButton, this));
    },

    onPrivacyMessageButton: function ()
    {
      this.$privacyMessageBlock.slideUp(100);
      $.get('/default/privacyMessageDismiss');
    }
  };

  $(function ()
  {
    var $node = $('body');
    if (0 < $node.length)
    {
      new PrivacyMessage($node.get(0));
    }
  });

})(window.jQuery);
