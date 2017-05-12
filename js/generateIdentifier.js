(function ($) {

  'use strict';

  var GenerateIdentifier = function ()
    {
      this.$generateIdentifierBtn = $('#generate-identifier');
      this.$identifier = $('#identifier');
      this.$maskEnabled = $('#using-identifier-mask');
      this.init();
    };

  GenerateIdentifier.prototype = {

    constructor: GenerateIdentifier,

    init: function()
    {
      this.$generateIdentifierBtn.on('click', $.proxy(this.genIdentifier, this));
      this.$identifier.on('input', $.proxy(this.identifierChanged, this));
    },

    genIdentifier: function(event)
    {
      var identifier = this.$identifier;
      var maskEnabled = this.$maskEnabled;
      event.preventDefault(); // Prevent page from jumping to top when clicking href="#"

      // Return deferred object
      return $.ajax({
        url: this.$generateIdentifierBtn.data('generate-identifier-url'),
        type: 'GET',
        success: function (data)
          {
            identifier.val(data['identifier']);
            // Freshly generated identifier, using mask!
            maskEnabled.attr('value', '1');
          }
      });
    },

    // If user changes identifier manually, we're no longer using an auto-generated
    // identifier from the mask, so do not increment counter. Using-mask will signal
    // to the action whether or not to increment the counter.
    identifierChanged: function(event)
    {
      this.$maskEnabled.attr('value', '0');
    }
  };

  $(function ()
  {
    new GenerateIdentifier();
  });
})(jQuery);
