(function ($) {

  "use strict";

  var ExportOptions = function (element)
    {
      this.$element = $(element);

      this.$type = this.$element.find('select[name="type"]');
      this.$formatSelect = this.$element.find('select[name="format"]');
      this.formatDescriptionCsv = this.$formatSelect.find('option[value="csv"]').text();
      this.formatDescriptionXml = this.$formatSelect.find('option[value="xml"]').text();
      this.$includeAllLevels = this.$element.find('input[name="includeAllLevels"]');
      if (0 != this.$includeAllLevels.length)
      {
        this.$includeAllLevelsHolder = this.$includeAllLevels.parent().parent();
      }
      this.$levelDiv = this.$element.find('div[id="exportLevels"]');
      if (0 != this.$levelDiv.length)
      {
        this.$levelDiv.hide();
      }
      this.$levelSelect = this.$element.find('select[id="levels"]');
      this.$includeDescendants = this.$element.find('input[name="includeDescendants"]');
      this.$includeDigitalObjects = this.$element.find('input[name="includeDigitalObjects"]');
      this.$includeDrafts = this.$element.find('input[name="includeDrafts"]');
      this.$exportSubmit = this.$element.find('input[id="exportSubmit"]');
      this.$exportDiv = this.$element.find('div[id="export-options"]');
      this.$genericHelpIcon = this.$element.find('a.generic-help-icon');
      this.animationMS = 250;
      this.init();
      this.listen();
    };


  ExportOptions.prototype = {

    constructor: ExportOptions,

    init: function()
    {
      this.resetLevelsOptions();
    },

    listen: function()
    {
      this.$type.on('change', $.proxy(this.onObjectTypeChange, this));
      if (0 != this.$genericHelpIcon.length)
      {
        this.$genericHelpIcon.on('click', $.proxy(this.toggleGenericHelp, this));
      }
      if (0 != this.$includeDescendants.length)
      {
        this.$includeDescendants.on('change', $.proxy(this.onExclusiveChange, this, 1));
      }
      if (0 != this.$includeDigitalObjects.length)
      {
        this.$includeDigitalObjects.on('change', $.proxy(this.onExclusiveChange, this, 2));
      }
      if (0 != this.$includeAllLevels.length)
      {
        this.$includeAllLevels.on('change', $.proxy(this.onIncludeAllLevelsChange, this));
      }
      this.$exportSubmit.on('click', $.proxy(this.onExportSubmit, this));
    },

    onExclusiveChange: function(field)
    {
      var collapseLevels = false;
      switch (field)
      {
        case 1:
          if (this.$includeDescendants.prop('checked'))
          {
            this.setDescendantsState(true);
            this.setDigitalObjectsState(false);
            this.$includeAllLevelsHolder.slideDown(this.animationMS);
          }
          else
          {
            collapseLevels = true;
            this.setDigitalObjectsState(true);
          }

          break;

        default:
          if (this.$includeDigitalObjects.prop('checked'))
          {
            this.setDescendantsState(false);
            this.setDigitalObjectsState(true);
            if (!this.$includeAllLevelsHolder.is(':animated'))
            {
              collapseLevels = true;
            }
          }
          else
          {
            this.setDescendantsState(true);
          }

          break;
      }
      if(collapseLevels)
      {
        var o = this;
        this.$includeAllLevelsHolder.slideUp(this.animationMS).promise().done(function()
        {
          o.resetLevelsOptions();
        });
      }
    },

    setDescendantsState: function(b)
    {
      var o = this.$includeDescendants.parent();
      if (b)
      {
        o.removeClass('muted');
      }
      else
      {
        this.$includeDescendants.attr('checked', false);
        o.addClass('muted');
      }
    },

    setDigitalObjectsState: function(b)
    {
      if (0 != this.$includeDigitalObjects.length)
      {
        var o = this.$includeDigitalObjects.parent();
        if (b)
        {
          o.removeClass('muted');
        }
        else
        {
          this.$includeDigitalObjects.attr('checked', false);
          o.addClass('muted');
        }
      }
    },

    resetLevelsOptions: function()
    {
      if (undefined != this.$includeAllLevelsHolder && 0 != this.$includeAllLevelsHolder.length)
      {
        this.$includeAllLevelsHolder.hide();
        this.$includeAllLevels.attr('checked', true);
      }
      if (undefined != this.$levelDiv && 0 != this.$levelDiv.length)
      {
        this.$levelDiv.slideUp(this.animationMS);
        this.$levelSelect.val('');
      }
    },

    onIncludeAllLevelsChange: function ()
    {
      this.$levelDiv.slideToggle(this.animationMS);
      if (this.$includeAllLevels.prop('checked'))
      {
        this.$levelSelect.val('');
      }
    },

    onObjectTypeChange: function ()
    {
      
      var url = window.location.href.split('?')[0] + '?type=';
      var type = this.$type.val().trim();
      switch (type)
      {
        case 'actor':
        case 'repository':
          url += type;

          break;

        default:
          url += 'informationObject';

          break;
      }
      window.location.href = url;
    },

    onExportSubmit: function ()
    {
      if (0 != this.$includeDescendants.length && !this.$includeAllLevels.prop('checked') && null == this.$levelSelect.val())
      {
        event.preventDefault();
        this.showAlert();
      }
    },

    showAlert: function()
    {
      // Show alert box in clipboard export page if it is not already added
      if ($('body').is('.clipboard.export') && $('#wrapper.container > .alert').length == 0)
      {
        $(
          '<div class="alert alert-error">' +
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
