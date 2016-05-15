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

      // Used to control loading status and block interface if needed
      this.setLoading(false);

      // Regular nodes selector
      this.nodesSelector = 'li:not(.ancestor, .more)';

      // Store the current resource id to highlight it
      // during the treeview browsing
      this.resourceId = this.$element.data('current-id');

      // Check if the treeview is sortable
      this.sortable = undefined !== this.$element.data('sortable') && !!this.$element.data('sortable');

      // Check if the treeview is used in the browser page
      this.browser = undefined !== this.$element.data('browser') && !!this.$element.data('browser');

      // Menu (tabs) and search box
      this.$menu = this.$element.prev('#treeview-menu');
      this.$search = this.$element.siblings('#treeview-search');
      this.$list = this.$element.siblings('#treeview-list');

      this.init();
    };

  Treeview.prototype = {

    constructor: Treeview,

    init: function()
      {
        this.$element
          .on('click.treeview.atom', 'li', $.proxy(this.click, this))
          .on('mousedown.treeview.atom', 'li', $.proxy(this.mousedownup, this))
          .on('mouseup.treeview.atom', 'li', $.proxy(this.mousedownup, this))
          .on('mouseenter.treeview.atom', 'li', $.proxy(this.mouseenter, this))
          .on('mouseleave.treeview.atom', 'li', $.proxy(this.mouseleave, this))
          .bind('scroll', $.proxy(this.scroll, this))
          .bind('scroll-debounced', $.proxy(this.debouncedScroll, this));

        this.$menu
          .on('click.treeview.atom', 'a', $.proxy(this.clickMenu, this));

        this.$search
          .on('submit.treeview.atom', 'form', $.proxy(this.search, this))
          .on('keydown.treeview.atom', 'input', $.proxy(this.searchChange, this));

        this.$list
          .on('click.treeview.atom', '.pager a', $.proxy(this.clickPagerButton, this));

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

        // For code simplicity we always show the popup in IE8 and IE10 (see #561 and #4910)
        if (!($.browser.msie && ($.browser.version == 8.0 || $.browser.version == 10.0)))
        {
          // Stop execution if the popover is not worth it
          if (($li.offset().left + $li.width()) >= ($anchor.offset().left + $anchor.width()))
          {
            return;
          }
        }

        // Pass function so the placement is computed every time
        $li.popover({ html: true, placement: function(popover, element) {
            return ($(window).innerWidth() - $(element).offset().left < 550) ? 'left' : 'right';
          }});

        $li.popover('show');

        // Hide title if empty
        var $title = $li.data('popover').$tip.find('h3');
        if (!$title.text().length)
        {
          $title.remove();
        }

        return this;
      },

    mousedownup: function (e)
      {
        if (this.loading)
        {
          killEvent(e);
        }

        return this;
      },

    mouseleave: function (e)
      {
        var $li = 'LI' === e.target.tagName ? $(e.target) : $(e.target).closest('li');

        $li.popover('hide');

        return this;
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

        if ($prev.is('.ancestor'))
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

        // When the [...] button is clicked
        if ($li.hasClass('more'))
        {
          killEvent(e);

          return this.showMore($li);
        }
        // When the arrow is clicked
        else if ('I' === e.target.tagName)
        {
          if ($li.hasClass('root'))
          {
            killEvent(e);

            return this;
          }

          return this.showItem($li);
        }

        return this;
      },

    showItem: function($element)
      {
        this.setLoading(true, $element);

        // Figure out if the user is try to collapse looking at the ancestor class
        var collapse = $element.hasClass('ancestor');

        // Check if the element has a previous ancestor
        var hasAncestor = $element.prev().hasClass('ancestor');

        // When collapsing a top-level item show prev and next siblings
        if (collapse && !hasAncestor)
        {
          var show = 'itemAndSiblings';
          var url = $element.data('xhr-location');
        }
        else
        {
          var show = 'item';
          var url = collapse ? $element.prev().data('xhr-location') : $element.data('xhr-location');
        }

        $.ajax({
          url: url,
          context: this,
          dataType: 'html',
          data: { show: show, resourceId: this.resourceId, browser: this.browser }})

          .fail(function (fail)
            {
              // Hide the expand icon if not found
              if (404 == fail.status)
              {
                $element
                  .removeClass('expand')
                  .children('i').remove();
              }
            })

          .done(function (data)
            {
              if (collapse && !hasAncestor)
              {
                $element.nextAll().remove();
                $element.replaceWith(data);
              }
              else if (collapse)
              {
                $element.nextAll().andSelf().remove();

                this.$element.find('.ancestor:last-child').after(data);
              }
              else
              {
                var nodes = this.$element.find(this.nodesSelector);
                var lastAncestor = nodes.eq(0).prev();

                // Check if is really an ancestor
                if (lastAncestor.hasClass('ancestor'))
                {
                  nodes.remove();
                  this.$element.find('.more').remove();
                  lastAncestor.after($element).next().addClass('ancestor').removeClass('expand').after(data);
                }
                else
                {
                  this.$element.find('.more').remove();
                  $element.addClass('ancestor').removeClass('expand');
                  var removeNodes = this.$element.find(this.nodesSelector);
                  removeNodes.remove();
                  $element.after(data);
                }
              }

              this.refreshSortableBehavior();
            })

          .always(function (data)
            {
              this.setLoading(false, $element);
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

        var showAction = $element.nextAll(':not(.popover):first').is('LI') ? 'prevSiblings' : 'nextSiblings';

        $.ajax({
          url: $element.data('xhr-location'),
          context: this,
          dataType: 'html',
          data: { show: showAction, resourceId: this.resourceId, browser: this.browser },
          beforeSend: function()
            {
              this.setLoading(true, $element);
            },
          success: function (data)
            {
              $element.replaceWith(data);

              this.refreshSortableBehavior();
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

    clickMenu: function (event)
      {
        event.stopPropagation();
        event.preventDefault();

        var $link = $(event.target);
        var $li = $link.parent();

        if (!$li.hasClass('active'))
        {
          this.$menu.find('li').removeClass('active');
          $li.addClass('active');
          this.$element.hide();
          this.$search.hide();
          this.$list.hide();

          $($link.data('toggle')).show();

          if ($link.data('toggle') == '#treeview-search')
          {
            this.$search.find('input').focus();
          }
        }
      },

    search: function (event)
      {
        event.preventDefault();

        var query = event.target.query.value;
        if (1 > query.length || this.loading)
        {
          return this;
        }

        // Obtain queryField value
        var queryField = this.$search.find('input[type="radio"][name="queryField"]:checked');
        if (queryField.length > 0)
        {
            var queryFieldValue = queryField.val();
            var data = { subquery: query, subqueryField: queryFieldValue };
        }
        else
        {
          var data = { query: query };
        }

        this.setLoading(true);

        $.ajax({
          url: event.target.action,
          context: this,
          dataType: 'json',
          data: data })

          .fail(function (fail)
            {
              if (404 == fail.status)
              {
                this.$search.find('.list-menu, .no-results').remove();
                this.$search.append('<div class="no-results">' + event.target.getAttribute('data-not-found') + '</div>');
              }
            })

          .done(function (data)
            {
              // Add new .list-menu
              this.$search.find('.list-menu, .no-results').remove();
              this.$search.append('<div class="list-menu"><ul></ul></div>');

              // Inject results, can we avoid .each()
              var $list = this.$search.find('.list-menu ul');
              for (var i in data.results)
              {
                var item = data.results[i];
                var link = '<a href="' + item.url + '">' + item.title + '</a>';
                $list.append('<li data-title="' + item.level + '" data-content="' + item.identifier + item.title + '"></li>').children(':last-child').append(link);
              }

              // Show more
              if (undefined !== data.more)
              {
                $list.after(data.more);
              }

              this.$search.find('.list-menu').addClass('open');

              // Show popover on mouse enter
              this.$search.on('mouseenter', 'li', function(e) {

                var $li = 'LI' === e.target.tagName ? $(e.target) : $(e.target).closest('li');

                // Pass function so the placement is computed every time
                $li.popover({ placement: function(popover, element) {
                    return ($(window).innerWidth() - $(element).offset().left < 550) ? 'left' : 'right';
                  }});

                $li.popover('show');
              });

              // Hide popover on mouse leave
              this.$search.on('mouseleave', 'li', function(e) {

                var $li = 'LI' === e.target.tagName ? $(e.target) : $(e.target).closest('li');

                $li.popover('hide');
              });
            })

          .always(function (data)
            {
              var self = this;
              window.setTimeout(function()
              {
                self.setLoading(false);
              }, 250);
            });

        return this;
      },

    searchChange: function (event)
      {
        switch (event.which)
        {
          case 27:
            this.$search.find('.list-menu, .no-results').remove();
            $(event.target).attr('value', '');
        }
      },

    clickPagerButton: function (event)
      {
        event.preventDefault();

        this.setLoading(true);

        $.ajax({
          url: event.target.href,
          context: this,
          dataType: 'json'})

          .fail(function (fail)
            {
              if (404 == fail.status)
              {
                this.$list.find('ul, section').remove();
              }
            })

          .done(function (data)
            {
              this.$list.find('ul, section').remove();
              this.$list.append('<ul></ul>');

              var $list = this.$list.find('ul');
              for (var i in data.results)
              {
                var item = data.results[i];
                var link = '<a href="' + item.url + '">' + item.title + '</a>';
                $list.append('<li></li>').children(':last-child').append(link);
              }

              // Show more
              if (undefined !== data.more)
              {
                $list.after(data.more);
              }
            })

          .always(function (data)
            {
              var self = this;
              window.setTimeout(function()
              {
                self.setLoading(false);
              }, 250);
            });

        return this;
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
