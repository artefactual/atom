(function ($) {

  "use strict";

  class Bs2DeprecationMessage {
    constructor(element) {
      this.$block = $(element);
      this.$button = this.$block.find('button');
      this.listen();
    }

    listen() {
      this.$button.on('click', $.proxy(this.onBs2DeprecationMessageButton, this));
    }

    onBs2DeprecationMessageButton() {
      this.$block.slideUp(100);
      $.get('/default/bs2DeprecationMessageDismiss');
    }
  }

  $(function ()
  {
    let $node = $('#bs2-deprecation-message');
    if (0 < $node.length)
    {
      new Bs2DeprecationMessage($node.get(0));
    }
  });

})(window.jQuery);
