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
    $element.find('select').prop('selectedIndex', 0);
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

      $('[id$=menu]').tooltip(
        {
          'placement': 'bottom'
        })
        .click(function ()
          {
            $(this).tooltip('hide');
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
   ****  Facets
   ****
   ****/

  $(document).ready(function()
    {
      var $facets = $('#facets');
      var $facet = $facets.find('.facet');

      $facet.on('click', '.facet-header p', function (e)
        {
          $(e.target).parents('.facet').toggleClass('open');
        });

      $facets.find('.facets-header a').click(function (e)
        {
          $(e.target).toggleClass('open');
          $facets.find('.content').toggle();
        });

      // Open first two facets
      $facet.slice(0, 2).filter(function(index, element)
        {
          return 0 < $(element).find('li').length;
        }).addClass('open');
    });

    $(document).ready(function () {
        $('.lod-filter [type=radio]').change(function (ev) {
            var link = ev.target.getAttribute('data-link');
            document.location.replace(link);
        });
    });

  /****
   ****
   ****  Date facets
   ****
   ****/

  $(document).ready(function()
    {
      var $form = $('.facet-date').find('form');

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

      $form.find('.facet-dates-clear').click(function (event)
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
      this.minLength = 3;

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
        this.$menu.on('mouseleave', 'li', $.proxy(this.mouseleave, this));
        this.$menu.on('click', 'li', $.proxy(this.click, this));

        this.$realm.on('mouseenter', 'div', $.proxy(this.mouseenter, this));
        this.$realm.on('mouseleave', 'div', $.proxy(this.mouseleave, this));
        this.$realm.on('change', 'input[type=radio]', $.proxy(this.changeRealm, this));

        // Validate form
        this.$form.submit(function (e)
          {
            // Forbid empty
            if (1 > e.target.query.value.length)
            {
              e.preventDefault();
              e.target.focus();
            }
          });
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

    changeRealm: function (e)
      {
        var $radio = $(e.target);
        if (undefined !== $radio.data('placeholder'))
        {
          this.$element.attr('placeholder', $radio.data('placeholder'));
        }
        else
        {
          var label = $(e.target).parent().text().trim();
          this.$element.attr('placeholder', label);
        }

        this.$element.focus();
      },

    showRealm: function (e)
      {
        this.hide();
        this.$realm.css('display', 'block');

        // Remove radius when the realm is shown
        this.$element.css('border-bottom-left-radius', 0);

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

        if (!query || query.length < this.minLength)
        {
          this.hide();
          this.showRealm();

          return this;
        }

        this.$element.addClass('loading');

        var radio = this.$form.find('[type=radio]:checked');
        var realm = radio.length ? radio.get(0).value : 'all';

        $.ajax(this.source,
          {
            context: this,
            data: { query: query, repos: realm },
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
            })
          .error(function()
            {
              this.$menu.slideUp('fast');
            })
          .always(function()
            {
              this.$element.removeClass('loading');
            });
      },

    render: function (html)
      {
        this.$menu.html(html);

        return this;
      },

    move: function (direction)
      {
        // Determine what dropdown is being displayed
        // and move through the items
        if (this.$menu.css('display') == 'block')
        {
          var $items = this.$menu.find('li');
          var $active = this.$menu.find('li.active:first');
        }
        else
        {
          var $items = this.$realm.find('div');
          var $active = this.$realm.find('div.active:first');
        }

        if ($active.length)
        {
          $active.removeClass('active');

          var pos = $items.index($active) + direction;
          if (pos >= 0)
          {
            $items.eq(pos).addClass('active');
          }
        }
        else
        {
          if (direction < 0)
          {
            $items.last().addClass('active');
          }
          else
          {
            $items.first().addClass('active');
          }
        }
      },

    select: function (e)
      {
        // Determine what dropdown is being displayed
        // and interact with the active element or submit the form
        if (this.$menu.css('display') == 'block')
        {
          var $active = this.$menu.find('li.active:first');
        }
        else
        {
          var $active = this.$realm.find('div.active:first');
        }

        if ($active.length)
        {
          var $radio = $active.find('input[type=radio]');
          if ($radio.length)
          {
            $radio.click();
          }
          else
          {
            $(location).attr('href', $active.find('a').attr('href'));
          }
        }
        else
        {
          this.$form.submit();
        }
      },

    keyup: function (e)
      {
        switch (e.keyCode)
        {
          case 40: // Down arrow
          case 38: // Up arrow
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
        switch (e.keyCode)
        {
          case 27: // Escape
            e.preventDefault();
            break;

          case 13: // Enter
            e.preventDefault();
            this.select();
            break;

          case 38: // Up arrow
            e.preventDefault();
            this.move(-1);
            break;

          case 40: // Down arrow
            e.preventDefault();
            this.move(1);
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
          }, 150);

        // Add placeholder as value in browsers without support
        if (!Modernizr.input.placeholder)
        {
          self.$element.val(self.$element.attr('placeholder'));
        }

        this.$form.removeClass('active');
      },

    focus: function (e)
      {
        if (!Modernizr.input.placeholder)
        {
          this.$element.val('');
        }

        this.showRealm();

        this.$form.addClass('active');

        return this;
      },

    mouseenter: function (e)
      {
        $(e.currentTarget).addClass('active');
      },

    mouseleave: function (e)
      {
        $(e.currentTarget).removeClass('active');
      },

    click: function (e)
      {
        e.preventDefault();
        $(location).attr('href', $(e.currentTarget).find('a').attr('href'));
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

  // Add placeholder as value in search box for browsers without support
  $(document).ready(function()
    {
      if (!Modernizr.input.placeholder)
      {
        $('#search-form-wrapper input[name="query"]').each(function()
          {
            var $this = $(this);

            // Ignore if it's already focus
            if ($this.is(':focus'))
            {
              return;
            }

            $this.val($this.attr('placeholder'));
          });
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
      this.$form = this.$element.find('form');
      this.$criteria = this.$element.find('.criteria');
      this.$filters = this.$element.find('#advanced-search-filters');

      this.init();
      this.listen();
    };

  AdvancedSearch.prototype = {

    constructor: AdvancedSearch,

    init: function()
    {
      // Hide first boolean
      this.$criteria.first().find('.boolean').hide();

      // Hide last criteria if more than once
      if (1 < this.$criteria.length)
      {
        this.$criteria.last().remove();
      }

      // Autoscroll to results
      var $article = this.$element.find('article');
      if ($article.length)
      {
        var pos = $article.first().offset().top;
        window.scrollTo(0, pos);
      }
    },

    listen: function()
    {
      this.$form
        .on('click', 'input.reset', $.proxy(this.reset, this))
        .on('submit', $.proxy(this.submit, this));

      // Bind events
      this.$element.on('click', $.proxy(this.click, this));
    },

    submit: function (event)
    {
      this.$filters.find('select').filter(function()
        {
          if (!this.value)
          {
            this.removeAttribute('name');
          }
        });
    },

    reset: function (event)
    {
      clearFormFields(this.$form);

      this.$element.find('.search-result').remove();
      this.$element.find('.criteria:not(:first)').remove();
      this.$element.find('.result-count').parent().remove();
    },

    click: function (event)
    {
      switch (event.target.id)
      {
        case 'add-criteria-and':
        case 'add-criteria-or':
        case 'add-criteria-not':
          event.preventDefault();

          this.addCriteria(event.target.  id.replace('add-criteria-', ''));

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
        .find('input, select').each(function(index, element)
          {
            var name = this.getAttribute('name').replace(/[\d+]/, nextNumber);
            this.setAttribute('name', name);
          }).end()
        .find('.boolean').show();

      clearFormFields($clone);

      return $clone;
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

  /****
   ****
   ****  Inline search
   ****
   ****/

  $(function ()
    {
      var $inlineSearch = $('.inline-search');

      $inlineSearch
        .on('click', '.dropdown-menu li a', function(e)
          {
            var $this = $(e.target);

            // Change button label
            $inlineSearch.find('.dropdown-toggle')
              .html($this.text() + '<span class="caret"></span>');

            // Modify subqueryField value
            $inlineSearch.find('#subqueryField')
              .val($this.text());
          })
        .on('keypress', 'input', function(e)
          {
            if (e.which == 13)
            {
              e.preventDefault();

              $inlineSearch.find('form').submit();
            }
          });
    });

  /****
   ****
   ****  Hide/show elements on click
   ****
   ****/

  $(function ()
    {
      $("#treeview-search-settings")
        .on('click', function(e)
          {
            e.preventDefault();

            $("#field-options").toggle(200);
          });

      $("#alternative-identifiers")
        .on('click', function(e)
          {
            e.preventDefault();

            $("#alternative-identifiers-table").toggle(200);
          });
    });

  /****
   ****
   ****  Disable/enable converseTerm field
   ****
   ****/

  $(function ()
    {
      var $selfReciprocal = $('input[id=selfReciprocal]');
      var $converseTerm = $('input[id=converseTerm]');

      if ($selfReciprocal.prop('checked'))
      {
        $converseTerm.prop('disabled', 'disabled').val('');
      }

      $selfReciprocal
        .on('change', function ()
          {
            if ($converseTerm.prop('disabled'))
            {
              $converseTerm.prop('disabled', false).focus();
            }
            else
            {
              $converseTerm.prop('disabled', 'disabled').val('');
            }
          });
    });

  /****
   ****
   ****  Settings menu sticker
   ****
   ****/

  $(function ()
    {
      var s = $("#sidebar > .settings-menu");
      var pos = s.position();
      $(window).scroll(function() {
        var windowpos = $(window).scrollTop();
        if (windowpos >= pos.top) {
          s.addClass("stick");
        } else {
          s.removeClass("stick");
        }
      });
    });

})(window.jQuery);

