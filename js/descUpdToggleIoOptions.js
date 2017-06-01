(function ($) {

  'use strict';

  // Show/hide information object options in the description
  // updates filters, depending on the class name selected

  var DescUpdToggleIoOptions = function ()
    {
      this.$className = $('select#className');
      this.$ioOptions = $('#io-options');
      this.init();
    };

  DescUpdToggleIoOptions.prototype = {

    constructor: DescUpdToggleIoOptions,

    init: function()
    {
      this.$className.on('change', $.proxy(this.onClassNameChange, this));
      this.onClassNameChange();
    },

    onClassNameChange: function()
    {
      switch (this.$className.val())
      {
        case 'QubitInformationObject':
          this.$ioOptions.show();

          break;

        default:
          this.$ioOptions.hide();

          break;
      }
    }

  };

  $(function ()
  {
    new DescUpdToggleIoOptions();
  });

})(jQuery);
