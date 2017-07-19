(function ($) {

  'use strict';

  var Clipboard = function (element)
    {
      this.$element = element;
      this.$menuButton = this.$element.find('> button');
      this.$menuHeaderCount = this.$element.find('.top-dropdown-header > span');
      this.$menuClearAll = this.$element.find('li#node_clearClipboard a');
      this.$toggleButtons = $('button.clipboard');

      this.init();
    };

  Clipboard.prototype = {

    constructor: Clipboard,

    init: function()
    {
      this.$toggleButtons.tooltip(
        {
          'placement': 'bottom',
          'container': 'body'
        });

      this.$toggleButtons.on('click', $.proxy(this.toggle, this, true));
      this.$menuClearAll.on('click', $.proxy(this.clear, this));

      // Listener for wide buttons must be added like this
      // as they are dynamically loaded in fullWidthTreeView.js
      $(document).on('click', 'button.clipboard-wide', $.proxy(this.toggle, this, false));
    },
    toggle: function (reloadTooltip, event)
    {
      if (typeof event.preventDefault === 'function')
      {
        event.preventDefault();
      }

      var $button = $(event.target);

      if (reloadTooltip)
      {
        $button.tooltip('hide');
      }

      // Return deferred object
      return $.ajax({
        url: $button.data('clipboard-url'),
        cache: false,
        data: { slug: $button.data('clipboard-slug') },
        context: this,
        beforeSend: function()
          {
            // Add loading gif
          },
        success: function (data)
          {
            this.updateButton($button, data.added, reloadTooltip);
            this.updateCounts(data.count, data.countByType);
          },
        error: function(error)
          {
            console.log(error);
          },
        complete: function()
          {
            // Remove loading gif
          }
      });
    },
    clear: function (event)
    {
      event.preventDefault();

      this.showAlert();

      $.ajax({
        url: this.$menuClearAll.attr('href'),
        type: 'DELETE',
        context: this,
        success: function (data)
          {
            if (data.success)
            {
              this.updateCounts(0);

              this.$toggleButtons.each($.proxy(function (index, button) {
                this.updateButton($(button), false, true);
              }, this));

              // Wide buttons must be selected like this as they
              // are dynamically loaded in fullWidthTreeView.js
              $(document).find('button.clipboard-wide').each($.proxy(function (index, button) {
                this.updateButton($(button), false, false);
              }, this));
            }
          },
        error: function(error)
          {
            console.log(error);
          }
      });
    },
    updateButton: function ($button, added, reloadTooltip)
    {
      // If previous and current status don't match,
      // change status, tooltip and button content
      if ((!$button.hasClass('added') && added)
        || ($button.hasClass('added') && !added))
      {
        // Show alert when removing
        if (!added)
        {
          this.showAlert();
        }

        $button.toggleClass('added');

        var label = $button.attr('data-title');
        var altLabel = $button.attr('data-alt-title');

        $button.attr('data-alt-title', label);
        $button.attr('data-title', altLabel);
        $button.text(altLabel);

        // Fix tooltip only in small buttons
        if (reloadTooltip)
        {
          $button.tooltip()
            .attr('data-original-title', altLabel)
            .tooltip('fixTitle');
        }
      }
    },
    updateCounts: function (count, countByType)
    {
      // Menu button count
      var $buttonSpan = this.$menuButton.find('> span');
      var countText = '';

      if (!$buttonSpan.length && count > 0)
      {
        this.$menuButton.append('<span>' + count + '</span>');
      }
      else if (count > 0)
      {
        $buttonSpan.text(count);
      }
      else if ($buttonSpan.length)
      {
        $buttonSpan.remove();
      }

      // Menu dropdown header count
      var informationObjectLabel = this.$menuHeaderCount.data('information-object-label');
      var actorLabel = this.$menuHeaderCount.data('actor-object-label');
      var repositoryLabel = this.$menuHeaderCount.data('repository-object-label');

      if (typeof(countByType) !== 'undefined') {
        countByType = JSON.parse(countByType);

        for (var key in countByType) {
          if (countByType.hasOwnProperty(key)) {
            switch (key)
            {
              case 'QubitInformationObject':
                countText += informationObjectLabel;
                break;
              case 'QubitActor':
                countText += actorLabel;
                break;
              case 'QubitRepository':
                countText += repositoryLabel;
                break;
              default:
                countText += 'Object';
                break;
            }
            countText += ' count: ' + countByType[key] + '<br />';
          }
        }

        this.$menuHeaderCount.html(countText);
      }
    },
    showAlert: function()
    {
      // Show alert box in clipboard page if it is not already added
      if ($('body').is('.user.clipboard') && $('#wrapper.container > .alert').length == 0)
      {
        $(
          '<div class="alert">' +
          '<button type="button" data-dismiss="alert" class="close">&times;</button>'
        )
        .append(this.$element.data('alert-message'))
        .prependTo($('#wrapper.container'));
      }
    }
  };

  $.fn.clipboard = function()
    {
      var $this = this;
      var data = $this.data('clipboard');
      if (!data)
      {
        $this.data('clipboard', new Clipboard(this));
      }
    };

  $.fn.clipboard.Constructor = Clipboard;

  $(function ()
    {
      var $clipboard = $('#clipboard-menu');

      if ($clipboard.length)
      {
        $clipboard.clipboard();
      }
    });

})(jQuery);
