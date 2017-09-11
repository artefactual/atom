(function ($) {

  "use strict";

  var CheckReposFilter = function (element)
    {
      this.$element = $(element);
      this.$reposFilter = this.$element.find('select[name="repos"]');
      this.$collectionFilter = this.$element.find('input[name="collection"]');
      this.$collectionFilterAutoComp = this.$element.find('input[id="collection"]');

      this.$skipUnmatched = this.$element.find('input[name="skipUnmatched"]');
      this.$skipMatched = this.$element.find('input[name="skipMatched"]');
      this.$noIndexSelect = this.$element.find('input[name="noIndex"]');

      this.$updateTypeSelect = this.$element.find('select[name="updateType"]');
      this.$objectTypeSelect = this.$element.find('select[name="objectType"]');

      this.$matchingPanel = this.$element.find('div[id="matchingOptions"]');
      this.$importAsNewPanel = this.$element.find('div[id="importAsNewOptions"]');
      this.$updateBlock = this.$element.find('div[id="updateBlock"]');
      this.$noIndexBlock = this.$element.find('div[id="noIndex"]');

      this.$repoLimitBlock = this.$element.find('div.repos-limit');
      this.$collectionLimitBlock = this.$element.find('div.collection-limit');

      this.init();
      this.listen();
    };

  CheckReposFilter.prototype = {

    constructor: CheckReposFilter,

    init: function()
    {
      this.toggleReposFilter();
      this.togglePanels();
      this.onObjectTypeChange();
    },

    listen: function()
    {
      this.$collectionFilter.on('change', $.proxy(this.toggleReposFilter, this));
      this.$updateTypeSelect.on('change', $.proxy(this.togglePanels, this));
      this.$objectTypeSelect.on('change', $.proxy(this.onObjectTypeChange, this));
    },

    onObjectTypeChange: function (event)
    {
      this.toggleNoIndex();
      this.toggleUpdateBlock();
    },

    toggleNoIndex: function (event)
    {
      switch (this.$objectTypeSelect.val())
      {
        case 'event':
          this.$noIndexBlock.hide();
          this.$noIndexSelect.attr('checked', false);
          break;

        default:
          this.$noIndexBlock.show();
          break;
      }
    },

    toggleReposFilter: function (event)
    {
      // Disable repository filter and agg if top-level description selected.
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

    togglePanels: function (event)
    {
      // Hide filter panel if default (import-as-new) is selected.
      if (this.$updateTypeSelect.val() == 'import-as-new')
      {
        this.resetMatchingBlock();
      }
      else
      {
        this.$reposFilter.removeAttr('disabled');
        this.$skipMatched.attr('checked', false);
        this.updateMatchingPanel();
      }
    },

    toggleUpdateBlock: function (event)
    {
      switch (this.$objectTypeSelect.val())
      {
        case 'informationObject':
        case 'authorityRecord':
        case 'ead':
        case 'eac-cpf':
        case 'repository':
          // Show updateBlock for these objectTypes.
          this.updateMatchingPanel();
          this.$updateBlock.show();
          break;

        default:
          this.$updateTypeSelect.val('import-as-new');
          this.resetMatchingBlock();

          // Do NOT show updateBlock for these objectTypes.
          this.$updateBlock.hide();
          break;
      }
    },

    updateMatchingPanel: function ()
    {
      if (this.$updateTypeSelect.val() == 'import-as-new')
      {
        this.resetMatchingBlock();
      }
      else
      {
        switch (this.$objectTypeSelect.val())
        {
          case 'authorityRecord':
          case 'eac-cpf':
            this.$collectionFilter.val('');
            this.$repoLimitBlock.show();
            this.$collectionLimitBlock.hide();
            break;

          case 'repository':
            this.$reposFilter.val('');
            this.$collectionFilter.val('');
            this.$repoLimitBlock.hide();
            this.$collectionLimitBlock.hide();
            break;

          default:
            this.$repoLimitBlock.show();
            this.$collectionLimitBlock.show();
        }

        this.$importAsNewPanel.hide();
        this.$matchingPanel.show();
      }
    },

    resetMatchingBlock: function ()
    {
      // Unset these fields so values are not accidentally POSTed.
      this.$reposFilter.val('');
      this.$collectionFilter.val('');
      this.$collectionFilterAutoComp.val('');
      this.$skipUnmatched.attr('checked', false);
      this.$skipMatched.attr('checked', false);

      this.$matchingPanel.hide();
      this.$importAsNewPanel.show();
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
