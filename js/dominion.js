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
   ****  Dropdown menus
   ****
   ****/

  $(function ()
    {
      // Stop propagation of dropdown menus so they don't get closed
      $('#user-menu .top-dropdown-container').click(
        function (e)
          {
            e.stopPropagation();
          });

      // TODO: focus() doesn't work
      $('#user-menu').on('click.dropdown.data-api', function(e)
        {
          var $menu = $(e.target).parent();
          if (!$menu.hasClass('open'))
          {
            $menu.find('#email').focus();
          }
        });
    });

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

  $(document).ready(function()
    {
      var $container = $('.masonry');
      $container.imagesLoaded(function() {
        $container.masonry({
          itemSelector: '.brick',
          isAnimated: false,
          gutterWidth: 15,
          isFitWidth: $container.hasClass('centered')
        });
      });
    });

  /****
   ****
   ****  jQuery Masonry
   ****
   ****/

  $(document).ready(function()
    {
      var $form = $('#facet-dates').find('form');

      $form.submit(function (e)
        {
          var $from = $(e.target.from);
          var $to = $(e.target.to);

          // Don't submit if empty
          if (!$from.get(0).value && !$to.get(0).value)
          {
            e.preventDefault();

            return;
          }

          // Parse document location and add current parameters to the form
          var uri = new URI();
          var uriParameters = uri.search(true);
          console.log(uriParameters);
          for (var key in uriParameters)
          {
            if (key == 'from' || key == 'to')
            {
              continue;
            }

            $('<input />')
              .attr('type', 'hidden')
              .attr('name', key)
              .attr('value', uriParameters[key])
              .appendTo($form);
          }
        });

      $form.find('#facet-dates-clear').click(function (event)
        {
          event.preventDefault();

          $form.find('input').attr('value', '');
          $form.get(0).submit();
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
      this.$realm = this.$element.parents('#search-form-wrapper').find('#search-realm');
      this.$form = this.$element.parents('form');
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

        // Remove radius when the realm is shown
        this.$element.css('border-bottom-left-radius', 0);

        return this;
      },

    hide: function()
      {
        this.$menu.hide();
        this.shown = false;

        // Use radius again
        this.$element.css('border-bottom-left-radius', '4px');

        return this;
      },

    showRealm: function (e)
      {
        this.hide();
        this.$realm.css('display', 'block');

        // Remove radius when the realm is shown
        this.$element.css('border-bottom-left-radius', 0);

        if (undefined === this.$realm.positioned)
        {
          var position = this.$element.offset();
          this.$realm.css('left', position.left);
          this.$realm.width(this.$element.innerWidth());

          this.$realm.positioned = true;
        }

        return this;
      },

    hideRealm: function (e)
      {
        this.$realm.css('display', 'none');

        // Use radius again
        this.$element.css('border-bottom-left-radius', '4px');

        return this;
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

        if (undefined === this.$menu.positioned)
        {
          var position = this.$element.offset();
          this.$menu.css('left', position.left);
          this.$menu.width(this.$element.innerWidth());

          this.$menu.positioned = true;
        }

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
        if (13 == e.keyCode && !e.target.value.length)
        {
          e.preventDefault();
          e.stopPropagation();

          return;
        }

        // if (!this.shown) return;

        switch (e.keyCode)
        {
          case 9: // Tab
          case 27: // Escape
            e.preventDefault();
            break;

          case 13:
            e.preventDefault();
            $(e.target).closest('form').get(0).submit();
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
            self.hideRealm();
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
      $('body').on('focus.qubit', '#search-form-wrapper input[name="query"]', function(e)
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
      var $advancedSearch = $('body.search.advanced');
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
