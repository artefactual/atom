(function ($) {

  "use strict";

  var ExportOptions = function (element)
    {

      this.$element = $(element);

      this.$levelDiv = this.$element.find('div[id="exportLevels"]');
      this.$levelSelect = this.$element.find('select[id="levels"]');
      this.$includeAllLevels = this.$element.find('input[name="includeAllLevels"]');
      this.$includeDescendants = this.$element.find('input[name="includeDescendants"]');
      this.$includeDrafts = this.$element.find('input[name="includeDrafts"]');
      this.$objectType = this.$element.find('select[name="objectType"]');
      this.$exportOptionsPanel = this.$element.find('div[id="exportOptions"]');
      this.$exportSubmit = this.$element.find('input[id="exportSubmit"]');
      this.$exportDiv = this.$element.find('div[id="export-options"]');
      this.$formatSelect = this.$element.find('select[name="format"]');
      this.$genericHelpIcon = this.$element.find('a.generic-help-icon');

      this.init();
      this.listen();
      this.onObjectTypeChange();
    };


  ExportOptions.prototype = {

    constructor: ExportOptions,

    init: function()
    {
      this.setDefaults();
    },

    listen: function()
    {
      this.$includeDescendants.on('change', $.proxy(this.onIncludeDescendantsChange, this));
      this.$includeAllLevels.on('change', $.proxy(this.onIncludeAllLevelsChange, this));
      this.$objectType.on('change', $.proxy(this.onObjectTypeChange, this));
      this.$exportSubmit.on('click', $.proxy(this.onExportSubmit, this));
      this.$genericHelpIcon.on('click', $.proxy(this.toggleGenericHelp, this));
    },

    setDefaults: function()
    {
      this.resetLevelsOptions();
    },

    onIncludeDescendantsChange: function ()
    {
      if (this.$includeDescendants.prop('checked'))
      {
        this.$includeAllLevels.attr('disabled', false);
      }
      else
      {
        this.resetLevelsOptions();
      }
    },

    resetLevelsOptions: function()
    {
      this.$includeAllLevels.attr('disabled', true);
      this.$includeAllLevels.attr('checked', true);
      this.$levelDiv.addClass('hidden');
      this.$levelSelect.val('');
    },

    onIncludeAllLevelsChange: function ()
    {
      this.$levelDiv.toggleClass('hidden');
      if (this.$includeAllLevels.prop('checked'))
      {
        this.$levelSelect.val('');
      }
    },

    onObjectTypeChange: function ()
    {
      /*
        - no xml export option when exporting repos
        - no csv export option when exporting auth recs
      */
      switch (this.$objectType.val())
      {
        case 'informationObject':
          this.$exportOptionsPanel.show();
          this.$formatSelect.find('option[value="csv"]').prop('selected', true);
          this.$formatSelect.find('option[value="csv"]').show();
          this.$formatSelect.find('option[value="xml"]').show();
          break;

        case 'actor':
          this.$formatSelect.find('option[value="xml"]').prop('selected', true);
          this.$formatSelect.find('option[value="csv"]').show();
          this.$formatSelect.find('option[value="xml"]').show();

          this.$exportOptionsPanel.hide();
          this.resetExportOptionsPanel();
          break;

        case 'repository':
          // hide xml option; select csv
          this.$formatSelect.find('option[value="csv"]').prop('selected', true);
          this.$formatSelect.find('option[value="csv"]').show();
          this.$formatSelect.find('option[value="xml"]').hide();

          this.$exportOptionsPanel.hide();
          this.resetExportOptionsPanel();
          break;

        default:
          this.$exportOptionsPanel.hide();
          this.resetExportOptionsPanel();
          break;
      }
    },

    resetExportOptionsPanel: function ()
    {
      this.$includeDescendants.attr('checked', false);
      this.$includeDrafts.attr('checked', false);
      this.$includeAllLevels.attr('disabled', true);
      this.$levelDiv.addClass('hidden');
      this.$includeAllLevels.attr('checked', true);
      this.$levelSelect.val('');
    },

    onExportSubmit: function ()
    {
      if (!this.$includeAllLevels.prop('checked') && null == this.$levelSelect.val())
      {
        event.preventDefault();
        this.showAlert();
      }
    },

    showAlert: function()
    {
      // Show alert box in clipboard export page if it is not already added
      if ($('body').is('.object.export') && $('#wrapper.container > .alert').length == 0)
      {
        $(
          '<div class="alert app-alert">' +
          '<button type="button" data-dismiss="alert" class="close">&times;</button>'
        )
        .append(this.$exportDiv.data('export-alert-message'))
        .prependTo($('#wrapper.container'));
      }
    },

    toggleGenericHelp: function (e)
    {
      e.preventDefault();

      var expanded = this.$genericHelpIcon.toggleClass('open').hasClass('open');
      this.$genericHelpIcon.attr('aria-expanded', expanded);

      $('.generic-help').toggle(400);
    }
  };

  $(function ()
  {
    var $node = $('body');
    if (0 < $node.length)
    {
      new ExportOptions($node.get(0));
    }
  });

})(window.jQuery);
