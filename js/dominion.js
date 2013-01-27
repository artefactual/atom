(function ($) {

  "use strict";

  /****
   ****
   ****  Tools
   ****
   ****/

  function clearFormFields($element)
  {
    $element.find('input:text, input:password, input:file, select').val('');
    $element.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
  }

  /****
   ****
   ****  Google Maps
   ****
   ****/

  $(function ()
    {
      var $container = $('.simple-map');

      if (!$container.length)
      {
        return;
      }

      window.initializeSimpleMap = function()
        {
          var location = new google.maps.LatLng($container.data('latitude'), $container.data('longitude'));
          var map = new google.maps.Map($container.get(0), {
              zoom: 16,
              center: location,
              panControl: false,
              mapTypeControl: true,
              zoomControl: true,
              scaleControl: false,
              streetViewControl: false,
              mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU },
              mapTypeId: google.maps.MapTypeId.ROADMAP,
              zoomControlOptions: { style: google.maps.ZoomControlStyle.SMALL },
            });
          var marker = new google.maps.Marker({ position: location, map: map});
        };

      $.getScript('https://maps.google.com/maps/api/js?sensor=false&callback=initializeSimpleMap&key=' + $container.data('key'));
    });

  /****
   ****
   ****  jQuery Masonry
   ****
   ****/

  $(function ()
    {
      var $container = $('.masonry');
      $container.imagesLoaded(function() {
        $container.masonry({
          itemSelector: '.brick',
          isAnimated: false
        });
      });
    });

  /****
   ****
   ****  Autocomplete plugin
   ****
   ****/

  var Autocomplete = function (element)
    {
      this.$element = element;
      this.$realm = this.$element.parent().find('#search-realm');
      this.$form = this.$element.parent('form');
      this.$menu = $('<div id="search-suggestions" class="search-popover"></div>').appendTo(this.$form);

      this.source = this.$element.closest('form').data('autocomplete');
      this.shown = false;
      this.timeout = 150;

      this.listen();
      this.showRealm();
    };

  Autocomplete.prototype = {

    constructor: Autocomplete,

    listen: function()
      {
        $(window)
          .on('resize', $.proxy(this.resize, this));

        this.$element
          .on('focus', $.proxy(this.focus, this))
          .on('blur', $.proxy(this.blur, this))
          .on('keypress', $.proxy(this.keypress, this))
          .on('keyup', $.proxy(this.keyup, this));

        if ($.browser.webkit || $.browser.msie)
        {
          this.$element.on('keydown', $.proxy(this.keypress, this));
        }

        this.$menu.on('mouseenter', 'li', $.proxy(this.mouseenter, this));
      },

    resize: function()
      {
        this.hide();
        this.hideRealm();
      },

    show: function()
      {
        this.hideRealm();
        this.$menu.show();
        this.shown = true;

        return this;
      },

    showRealm: function (e)
      {
        this.hide();
        this.$realm.show();

        return this;
      },

    hide: function()
      {
        this.$menu.hide();
        this.shown = false;

        return this;
      },

    hideRealm: function (e)
      {
        this.$realm.hide();
      },

    lookup: function (e)
      {
        var query = this.$element.val();

        if (!query)
        {
          this.hide();
          this.showRealm();

          return this;
        }

        $.ajax(this.source,
          {
            context: this,
            data: { query: query },
            dataType: 'html'
          })
          .done(function(html)
            {
              if (html)
              {
                this.render(html).show();
              }
              else
              {
                this.hide();
              }
            });
      },

    render: function (html)
      {
        this.$menu.html(html);

        return this;
      },

    next: function (e) { },
    prev: function (e) { },
    select: function (e) { },

    keyup: function (e)
      {
        switch (e.keyCode)
        {
          case 40: // Down arrow
          case 38: // Up arrow
            break;

          case 9: // Tab
            if (!this.shown)
            {
              return;
            }
            this.select();
            break;

          case 27: // Escape
            this.$element.val('');
            this.hide();
            this.hideRealm();
            break;

          default:
            if (this.timer)
            {
              clearTimeout(this.timer);
            }
            var self = this;
            this.timer = setTimeout(function()
              {
                self.lookup();
              }, this.timeout);
        }

        e.stopPropagation();
        e.preventDefault();
      },

    keypress: function (e)
      {
        if (!this.shown)
        {
          return;
        }

        switch (e.keyCode)
        {
          case 9: // Tab
          case 27: // Escape
            e.preventDefault();
            break;

          case 38: // Up arrow
            e.preventDefault();
            this.prev();
            break;

          case 40: // Down arrow
            e.preventDefault();
            this.next();
            break;
        }

        e.stopPropagation();
      },

    blur: function (e)
      {
        var self = this;
        setTimeout(function ()
          {
            self.hide();
            self.$realm.hide();
            self.$element.val('');
          }, 150);

        this.$form.removeClass('active');
      },

    focus: function (e)
      {
        this.$element.val('');
        this.showRealm();

        this.$form.addClass('active');

        return this;
      },

    mouseenter: function (e)
      {
        this.$menu.find('active').removeClass('active');
        $(e.currentTarget).addClass('active');
      }
  };

  $.fn.autocomplete = function()
    {
      var $this = this;
      var data = $this.data('autocomplete');
      if (!data)
      {
        $this.data('autocomplete', new Autocomplete(this));
      }
    };

  $.fn.autocomplete.Constructor = Autocomplete;

  $(function ()
    {
      $('body').on('focus.qubit', '#top-bar-search input[name="query"]', function(e)
        {
          var $this = $(this);

          if ($this.data('autocomplete'))
          {
            return;
          }

          e.preventDefault();
          $this.autocomplete();
        });
    });

  /****
   ****
   ****  Treeview plugin
   ****
   ****/

  var Treeview = function (element)
    {
      this.$element = element;
      this.$showAllButton = this.$element.find('li:first');
      this.loading = false;

      this.init();
    };

  Treeview.prototype = {

    constructor: Treeview,

    init: function()
      {
        this.$element
          .on('click.treeview.qubit', 'li', $.proxy(this.click, this))
          .on('mouseenter.treeview.qubit', 'li', $.proxy(this.mouseenter, this))
          .on('mouseleave.treeview.qubit', 'li', $.proxy(this.mouseleave, this));

        $('#navigation').bind('scroll', $.proxy(this.scroll, this));
      },

    scroll: function (e)
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
              self.$element.find('li.more:last').trigger('click');
            }, 250);
        }
      },

    mouseenter: function (e)
      {
        $(e.target).addClass('hover');
      },

    mouseleave: function (e)
      {
        $(e.target).removeClass('hover');
      },

    click: function(e)
      {
        if (this.loading)
        {
          return;
        }

        var $li = 'LI' === e.target.tagName ? $(e.target) : $(e.target).closest('li');

        if ($li.hasClass('back'))
        {
          e.preventDefault();
          e.stopPropagation();

          return this.showAll($li);
        }
        else if ($li.hasClass('ancestor'))
        {
          e.preventDefault();
          e.stopPropagation();

          if (!$li.next().hasClass('ancestor'))
          {
            return this;
          }

          return this.showAncestor($li);
        }
        else if ($li.hasClass('expand'))
        {
          e.preventDefault();
          e.stopPropagation();

          return this.showItem($li);
        }
        else if ($li.hasClass('more'))
        {
          e.preventDefault();
          e.stopPropagation();

          return this.showMore($li);
        }

        return this;
      },

    showAll: function ($element)
      {
        $.ajax({
          url: $element.data('xhr-location'),
          context: this,
          dataType: 'html',
          data: { show: 'all' },
          beforeSend: function ()
            {
              this.loading = true;
            },
          success: function (data)
            {
              $element
                .hide()
                .nextAll().remove().end()
                .after(data).end();
            },
          complete: function ()
            {
              this.loading = false;
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
          data: { show: 'item' },
          beforeSend: function ()
            {
              this.loading = true;
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
            },
          complete: function ()
            {
              this.loading = false;
            },
          error: function ()
            {
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
          data: { show: 'item' },
          beforeSend: function ()
            {
              this.loading = true;
            },
          success: function (data)
            {
              $element

                // Remove all the nodes below
                .nextAll().remove().end()

                // Add new nodes
                .after(data).end();
            },
          complete: function ()
            {
              this.loading = false;
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
          data: { show: !$element.next().length ? 'nextSiblings' : 'prevSiblings' },
          beforeSend: function()
            {
              this.loading = true;
            },
          success: function (data)
            {
              $element.replaceWith(data);
            },
          complete: function ()
            {
              this.loading = false;

              window.clearTimeout(loadingId);
            },
          error: function ()
            {
            }
          });
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

  /****
   ****
   ****  Advanced search
   ****
   ****/

  var AdvancedSearch = function (element)
    {
      this.$element = $(element);

      this.listen();
    };

  AdvancedSearch.prototype = {

    constructor: AdvancedSearch,

    listen: function()
    {
      this.$criteria = this.$element.find('.criteria');
      this.$filters = this.$element.find('#filters');

      // Hide first boolean
      this.$criteria.first().find('.boolean').hide();

      // Hide last criteria if more than once
      if (1 < this.$criteria.length)
      {
        this.$criteria.last().hide();
      }

      // Hide filters if not being used
      if (!this.$element.find('#toggle-filters').hasClass('active'))
      {
        this.$filters.hide();
      }

      // Bind events
      this.$element.on('click', $.proxy(this.click, this));
    },

    click: function (event)
    {
      var $target = $(event.target);
      var id = $target.attr('id');

      switch (id)
      {
        case 'add-criteria-and':
        case 'add-criteria-or':
        case 'add-criteria-not':
          event.preventDefault();
          this.addCriteria(id.replace('add-criteria-', ''));
          break;

        case 'toggle-filters':
          event.preventDefault();
          this.$filters.slideToggle('fast');
          $target.toggleClass('active');
          if (!$target.hasClass('active'))
          {
            clearFormFields(this.$filters);
          }
          break;
      }
    },

    addCriteria: function (option)
    {
      this
        .cloneLastCriteria()
        .insertAfter(this.getLastCriteria())
        .show()
        .find('.boolean select').val(option).end()
        .find('.criterion input').first().focus();
    },

    getLastCriteria: function()
    {
      return this.$element.find('.criteria:last');
    },

    cloneLastCriteria: function()
    {
      var $clone = this.getLastCriteria().clone();

      var nextNumber = parseInt($clone.find('input:first').attr('name').match(/\d+/).shift()) + 1;

      $clone
        .find('input, select').each(function()
          {
            var $this = $(this);
            $this.attr('name', $this.attr('name').replace(/\[\d+\]/, '[' + nextNumber +']'));
          }).end()
        .find('.boolean').show();

      return this.resetFormFields($clone);
    },

    resetFormFields: function($sender)
    {
      return $sender
        .find('input:text, input:password, input:file, select')
          .val('')
          .end()
        .find('input:radio, input:checkbox')
          .removeAttr('checked').removeAttr('selected')
          .end();
    }
  };

  $(function ()
    {
      var $advancedSearch = $('#advanced-search');
      if (0 < $advancedSearch.length)
      {
        new AdvancedSearch($advancedSearch.get(0));
      }

      var $body = $('body.search');
      if ($body.hasClass('index') || $body.hasClass('advanced'))
      {
        $body.find('[name^=creationDate]').on('keyup', function (e)
          {
            // Enter
            if (e.keyCode == 13)
            {
              e.preventDefault();
              $(e.target).closest('form').submit();
            }
          });
      }
    });

})(window.jQuery);
