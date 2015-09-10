(function ($) {

  'use strict';

  var Clipboard = function (element)
    {
      this.$element = element;
      this.$menuButton = this.$element.find('> button');
      this.$menuHeaderCount = this.$element.find('.top-dropdown-header > span');
      this.$menuClearAll = this.$element.find('li#node_clearClipboard a');
      this.$toggleButtons = $('button.clipboard');
      this.$toggleWideButtons = $('button.clipboard-wide');

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
      this.$toggleWideButtons.on('click', $.proxy(this.toggle, this, false));
      this.$menuClearAll.on('click', $.proxy(this.clear, this));
    },
    toggle: function (reloadTooltip, event)
    {
      event.preventDefault();

      var $button = $(event.target);
      
      if (reloadTooltip)
      {
        $button.tooltip('hide');
      }

      $.ajax({
        url: $button.data('clipboard-url'),
        data: { slug: $button.data('clipboard-slug') },
        context: this,
        beforeSend: function()
          {
            // Add loading gif
          },
        success: function (data)
          {
            this.updateButton($button, data.added, reloadTooltip);
            this.updateCounts(data.count);
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

              this.$toggleWideButtons.each($.proxy(function (index, button) {
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
    updateCounts: function (count)
    {
      // Menu button count
      var $buttonSpan = this.$menuButton.find('> span');
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
      var pluralLabel = this.$menuHeaderCount.data('plural-label');
      var singleLabel = this.$menuHeaderCount.data('single-label');
      if (count != 1)
      {
        this.$menuHeaderCount.text(count + ' ' + pluralLabel);
      }
      else
      {
        this.$menuHeaderCount.text(count + ' ' + singleLabel);
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
