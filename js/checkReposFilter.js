(function ($) {

  "use strict";

  var CheckReposFilter = function (element)
    {
      this.$element = $(element);
      this.$reposFilter = this.$element.find('select[name="repos"]');
      this.$collectionFilter = this.$element.find('input[name="collection"]');
      this.$collectionFilterAutoComp = this.$element.find('input[id="collection"]');
      this.$filterPanel = this.$element.find('div[id="matchingOptions"]');
      this.$skipUnmatched = this.$element.find('input[name="skipUnmatched"]');
      this.$updateTypeSelect = this.$element.find('select[name="updateType"]');

      this.init();
      this.listen();
    };

  CheckReposFilter.prototype = {

    constructor: CheckReposFilter,

    init: function()
    {
      this.toggleReposFilter();
      this.toggleFilterPanel();
    },

    listen: function()
    {
      this.$collectionFilter.on('change', $.proxy(this.toggleReposFilter, this));

      this.$updateTypeSelect.on('change', $.proxy(this.toggleFilterPanel, this));
    },

    toggleReposFilter: function (event)
    {
      // Disable repository filter and facet if top-level description selected
      if (this.$reposFilter.length && this.$collectionFilter.val() != '')
      {
        this.$reposFilter.attr("disabled", "disabled");
        this.$reposFilter.val('');
      }
      else if (this.$reposFilter.length && this.$collectionFilter.val() == '')
      {
        this.$reposFilter.removeAttr('disabled');
      }
    },

    toggleFilterPanel: function (event)
    {
      // Hide filter panel if default (importAsNew) is selected.
      if (this.$updateTypeSelect.val() == 'importAsNew')
      {
        this.$reposFilter.val('');
        this.$collectionFilter.val('');
        this.$collectionFilterAutoComp.val('');
        this.$skipUnmatched.attr('checked', false);;

        this.$filterPanel.hide();
      }
      else if (this.$updateTypeSelect.val() != 'importAsNew')
      {
        this.$reposFilter.removeAttr('disabled');

        this.$filterPanel.show();
      }
    }

  };

  $(function ()
  {
    var $node = $('body');
    if (0 < $node.length)
    {
      new CheckReposFilter($node.get(0));
    }
  });

})(window.jQuery);
