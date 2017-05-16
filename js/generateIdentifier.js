(function ($) {

  'use strict';

  var GenerateIdentifier = function ()
    {
      this.$generateIdentifierBtn = $('#generate-identifier');
      this.$identifier = $('#identifier');
      this.init();
    };

  GenerateIdentifier.prototype = {

    constructor: GenerateIdentifier,

    init: function()
    {
      this.$generateIdentifierBtn.on('click', $.proxy(this.genIdentifier, this));
    },

    genIdentifier: function (event)
    {
      var identifier = this.$identifier;

      // Return deferred object
      return $.ajax({
        url: this.$generateIdentifierBtn.data('endpoint-url'),
        type: 'GET',
        success: function (data)
          {
            identifier.val(data['identifier']);
          }
      });
    }
  };

  $(function ()
  {
    new GenerateIdentifier();
  });
})(jQuery);
