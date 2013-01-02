(function ($) {

  "use strict";

  /**
   * Debounces a function. Returns a function that calls the original fn function only if no invocations have been made
   * within the last quietMillis milliseconds.
   *
   * @param quietMillis number of milliseconds to wait before invoking fn
   * @param fn function to be debounced
   * @return debounced version of fn
   */
  function debounce (quietMillis, fn)
  {
    var timeout;
    return function ()
      {
        window.clearTimeout(timeout);
        timeout = window.setTimeout(fn, quietMillis);
      };
  }

  function killEvent (event)
  {
    event.preventDefault();
    event.stopPropagation();
  }

  function indexOf (value, array)
  {
    var i = 0, l = array.length, v;

    if (typeof value === "undefined")
    {
      return -1;
    }

    if (value.constructor === String)
    {
      for (; i < l; i = i + 1)
      {
        if (value.localeCompare(array[i]) === 0)
        {
          return i;
        }
      }
    }
    else
    {
      for (; i < l; i = i + 1)
      {
        v = array[i];
        if (v.constructor === String)
        {
          if (v.localeCompare(value) === 0)
          {
            return i;
          }
        }
        else
        {
          if (v === value)
          {
            return i;
          }
        }
      }
    }

    return -1;
  }

  var Treeview = function (element)
    {
      this.$element = element;
      this.$showAllButton = this.$element.find('li:first');

      // Used to control loading status and block interface if needed
      this.setLoading(false);

      // Regular nodes selector
      this.nodesSelector = 'li:not(.back, .ancestor, .more)';

      // Store the current resource id to highlight it
      // during the treeview browsing
      this.resourceId = this.$element.data('current-id');

      // Check if the treeview is sortable
      this.sortable = undefined !== this.$element.data('sortable') && !!this.$element.data('sortable');

      this.init();
    };

  Treeview.prototype = {

    constructor: Treeview,

    init: function()
      {
        this.$element
          .on('click.treeview.qubit', 'li', $.proxy(this.click, this))
          .on('mousedown.treeview.qubit', 'li', $.proxy(this.mousedownup, this))
          .on('mouseup.treeview.qubit', 'li', $.proxy(this.mousedownup, this))
          .on('mouseenter.treeview.qubit', 'li', $.proxy(this.mouseenter, this))
          // .on('mouseleave.treeview.qubit', 'li', $.proxy(this.mouseleave, this))
          .bind('scroll', $.proxy(this.scroll, this))
          .bind('scroll-debounced', $.proxy(this.debouncedScroll, this));

        // Prevent out-of-bounds scrollings via mousewheel
        if ($.fn.mousewheel)
        {
          this.$element.bind('mousewheel', $.proxy(this.mousewheel, this));
        }

        var self = this;
        this.notify = debounce(80, function (e)
          {
            self.$element.trigger('scroll-debounced', e);
          });

        this.installSortableBehavior();

        this.highlightLastAncestor();
      },

    setLoading: function (status, $node)
      {
        this.loading = status;

        if (this.loading)
        {
          this.$element.addClass('loading');

          if ($node)
          {
            // Add loading icon
            $node.append('<div class="loading" />');
            $node.children('i').css('visibility', 'hidden');
          }
        }
        else
        {
          this.$element.removeClass('loading');

          if ($node)
          {
            // Remove loading icon
            $node
              .children('.loading').remove().end()
              .children('i').css('visibility', 'visible');
          }

          // Remove popups
          $('.popover').remove();
        }

        return this;
      },

    installSortableBehavior: function ()
      {
        // Create jquery-ui sortable object
        if (!this.sortable)
        {
          return this;
        }

        this.$element.sortable(
          {
            items: this.nodesSelector,
            placeholder: 'placeholder',
            forcePlaceholderSize: true,
            start: $.proxy(this.drag, this),
            stop: $.proxy(this.drop, this),
            axis: 'y'
          });

        this.$element.disableSelection();

        this.showGrip();
      },

    refreshSortableBehavior: function ()
      {
        // Create jquery-ui sortable object
        if (!this.sortable)
        {
          return this;
        }

        var nodes = this.$element.find(this.nodesSelector);

        if (1 < nodes)
        {
          nodes.sortable('refresh');
        }

        this.showGrip();

        return this;
      },

    showGrip: function ()
      {
        this.$element
          .find('.grip').remove().end()
          .find(this.nodesSelector).prepend('<small class="grip"></small>');

        return this;
      },

    mouseenter: function (e)
      {
        var $li = 'LI' === e.target.tagName ? $(e.target) : $(e.target).closest('li');
        var $anchor = $li.children('a');

        // For code simplicity we always show the popup in IE8 (see #561)
        if (!($.browser.msie && $.browser.version == 8.0))
        {
          // Stop execution if the popover is not worth it
          if (($li.offset().left + $li.width()) >= ($anchor.offset().left + $anchor.width()))
          {
            return;
          }
        }

        // Pass function so the placement is computed every time
        $li.popover({ placement: function(popover, element) {
            return ($(window).innerWidth() - $(element).offset().left < 550) ? 'left' : 'right';
          }});

        $li.popover('show');

        return this;
      },

    // mouseleave: function (e) { return this; },

    mousedownup: function (e)
      {
        if (this.loading)
        {
          killEvent(e);
        }
      },

    drag: function (e, ui)
      {
        this._position = ui.item.prev().index();

        // Remove popups
        $('.popover').remove();
      },

    drop: function (e, ui)
      {
        if (this._position == ui.item.prev().index())
        {
          return this;
        }

        var $prev = ui.item.prev();
        var $next = ui.item.next();

        var data = {};

        if ($prev.is('.back, .ancestor'))
        {
          data = { move: 'moveBefore', target: $next.data('xhr-location') };
        }
        else
        {
          data = { move: 'moveAfter', target: $prev.data('xhr-location') };
        }

        $.ajax({
          url: ui.item.data('xhr-location').replace(/treeView$/, 'treeViewSort'),
          context: this,
          dataType: 'html',
          data: data,
          beforeSend: function ()
            {
              this.setLoading(true, ui.item);
            },
          success: function ()
            {
              // Green highlight effect
              ui.item.effect("highlight", { color: '#dff0d8' }, 500);
            },
          complete: function ()
            {
              this.setLoading(false, ui.item);
            },
          error: function (jqXHR, textStatus, thrownError)
            {
              // Cancel event if HTTP error
              // Item will be moved back to its original position
              if (thrownError.length)
              {
                this.$element.sortable('cancel');
              }

              // Red highlight effect
              ui.item.effect("highlight", { color: '#f2dede' }, 500);
            }
          });

        return this;
      },

    mousewheel: function (e, delta, deltaX, deltaY)
      {
        var top = this.$element.scrollTop(), height;
        if (deltaY > 0 && top - deltaY <= 0)
        {
          this.$element.scrollTop(0);
          killEvent(e);
        }
        else if (deltaY < 0 && this.$element.get(0).scrollHeight - this.$element.scrollTop() + deltaY <= this.$element.height())
        {
          this.$element.scrollTop(this.$element.get(0).scrollHeight - this.$element.height());
          killEvent(e);
        }
      },

    scroll: function (e)
      {
        if (indexOf(e.target, this.$element.get()) >= 0)
        {
          this.notify(e);
        }
      },

    debouncedScroll: function (e)
      {
        var $target = $(e.target);

        e.preventDefault();

        // Detect when users scrolls to the bottom
        if ($target.scrollTop() + $target.innerHeight() >= $target.get(0).scrollHeight)
        {
          var self = this;

          // Delay the trigger
          window.setTimeout(function()
            {
              var $more = self.$element.find('.more:last');

              // Make sure that we have selected the nextSiblings button
              if (0 < $more.next().length)
              {
                return;
              }

              $more.trigger('click');

            }, 250);
        }
      },

    click: function (e)
      {
        var $li = 'LI' === e.target.tagName ? $(e.target) : $(e.target).closest('li');

        if (this.loading && 'A' !== e.target.tagName)
        {
          killEvent(e);

          return;
        }

        // Expand button
        if ($li.hasClass('more'))
        {
          killEvent(e);

          return this.showMore($li);
        }
        // Back to an ancestor
        else if ($li.hasClass('back'))
        {
          killEvent(e);

          return this.showAll($li);
        }
        else if ('I' === e.target.tagName)
        {
          if ($li.hasClass('immediate-ancestor'))
          {
            $li.prev().find('i').trigger('click');
          }
          else if ($li.hasClass('ancestor'))
          {
            if (!$li.next().hasClass('ancestor'))
            {
              return this;
            }

            return this.showAncestor($li);
          }
          else if ($li.hasClass('expand'))
          {
            return this.showItem($li);
          }
        }

        return this;
      },

    showAll: function ($element)
      {
        $.ajax({
          url: $element.data('xhr-location'),
          context: this,
          dataType: 'html',
          data: { show: 'all', resourceId: this.resourceId },
          beforeSend: function ()
            {
              this.setLoading(true, $element);
            },
          success: function (data)
            {
              $element
                .hide()
                .nextAll().remove().end()
                .after(data);

              this.refreshSortableBehavior();

              this.highlightLastAncestor();
            },
          complete: function ()
            {
              this.setLoading(false, $element);
            },
          error: function ()
            {
            }
          });

        return this;
      },

    showItem: function($element)
      {
        $.ajax({
          url: $element.data('xhr-location'),
          context: this,
          dataType: 'html',
          data: { show: 'item', resourceId: this.resourceId },
          beforeSend: function ()
            {
              this.setLoading(true, $element);
            },
          success: function (data)
            {
              this.$showAllButton

                // Show "Show all" button
                .show()

                // Move cursor to last ancestor
                .nextAll(':not(.ancestor,.showall):first').prev()

                // Remove all siblings below
                .nextAll().remove().end()

                // Add new nodes
                .after(data)

                // Expanded node becomes now an ancestor
                .after($element).next()
                .removeClass('expand').addClass('ancestor');

              this.refreshSortableBehavior();

              this.highlightLastAncestor();
            },
          complete: function ()
            {
              this.setLoading(false, $element);
            },
          error: function (fail)
            {
              // Rare situation where any children is visible
              // Hide the expand icon
              if (404 == fail.status)
              {
                $element
                  .removeClass('expand')
                  .children('i').remove();
              }
            }
          });

        return this;
      },

    showAncestor: function($element)
      {
        $.ajax({
          url: $element.data('xhr-location'),
          context: this,
          dataType: 'html',
          data: { show: 'item', resourceId: this.resourceId },
          beforeSend: function ()
            {
              this.setLoading(true, $element);
            },
          success: function (data)
            {
              $element

                // Remove all the nodes below
                .nextAll().remove().end()

                // Add new nodes
                .after(data);

              this.refreshSortableBehavior();

              this.highlightLastAncestor();
            },
          complete: function ()
            {
              this.setLoading(false, $element);
            },
          error: function ()
            {
            }
          });

        return this;
      },

    showMore: function($element)
      {
        var $a = $element.find('a');
        var loadingId = window.setInterval(function()
          {
            $a.append('.');
          }, 125);

        $.ajax({
          url: $element.data('xhr-location'),
          context: this,
          dataType: 'html',
          data: { show: !$element.next().length ? 'nextSiblings' : 'prevSiblings', resourceId: this.resourceId },
          beforeSend: function()
            {
              this.setLoading(true, $element);
            },
          success: function (data)
            {
              $element.replaceWith(data);

              this.refreshSortableBehavior();

              this.highlightLastAncestor();
            },
          complete: function ()
            {
              this.setLoading(false, $element);

              window.clearTimeout(loadingId);
            },
          error: function ()
            {
            }
          });
      },

    highlightLastAncestor: function()
      {
        // Unfortunately I couldn't do this with CSS
        this.$element
          .find('.ancestor').removeClass('immediate-ancestor')
          .last().addClass('immediate-ancestor');
      }
  };

  $.fn.treeview = function()
    {
      var $this = this;
      var data = $this.data('treeview');
      if (!data)
      {
        $this.data('treeview', new Treeview(this));
      }
    };

  $.fn.treeview.Constructor = Treeview;

  $(function ()
    {
      var $treeview = $('#treeview');
      if (0 < $treeview.length)
      {
        $treeview.treeview();
      }
    });

})(window.jQuery);

/*! Copyright (c) 2011 Brandon Aaron (http://brandonaaron.net)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 * Thanks to: Seamus Leahy for adding deltaX and deltaY
 *
 * Version: 3.0.6
 *
 * Requires: 1.2.2+
 */

(function($) {

var types = ['DOMMouseScroll', 'mousewheel'];

if ($.event.fixHooks) {
    for ( var i=types.length; i; ) {
        $.event.fixHooks[ types[--i] ] = $.event.mouseHooks;
    }
}

$.event.special.mousewheel = {
    setup: function() {
        if ( this.addEventListener ) {
            for ( var i=types.length; i; ) {
                this.addEventListener( types[--i], handler, false );
            }
        } else {
            this.onmousewheel = handler;
        }
    },

    teardown: function() {
        if ( this.removeEventListener ) {
            for ( var i=types.length; i; ) {
                this.removeEventListener( types[--i], handler, false );
            }
        } else {
            this.onmousewheel = null;
        }
    }
};

$.fn.extend({
    mousewheel: function(fn) {
        return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel");
    },

    unmousewheel: function(fn) {
        return this.unbind("mousewheel", fn);
    }
});


function handler(event) {
    var orgEvent = event || window.event, args = [].slice.call( arguments, 1 ), delta = 0, returnValue = true, deltaX = 0, deltaY = 0;
    event = $.event.fix(orgEvent);
    event.type = "mousewheel";

    // Old school scrollwheel delta
    if ( orgEvent.wheelDelta ) { delta = orgEvent.wheelDelta/120; }
    if ( orgEvent.detail     ) { delta = -orgEvent.detail/3; }

    // New school multidimensional scroll (touchpads) deltas
    deltaY = delta;

    // Gecko
    if ( orgEvent.axis !== undefined && orgEvent.axis === orgEvent.HORIZONTAL_AXIS ) {
        deltaY = 0;
        deltaX = -1*delta;
    }

    // Webkit
    if ( orgEvent.wheelDeltaY !== undefined ) { deltaY = orgEvent.wheelDeltaY/120; }
    if ( orgEvent.wheelDeltaX !== undefined ) { deltaX = -1*orgEvent.wheelDeltaX/120; }

    // Add event and delta to the front of the arguments
    args.unshift(event, delta, deltaX, deltaY);

    return ($.event.dispatch || $.event.handle).apply(this, args);
}

})(jQuery);
