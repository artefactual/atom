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
    $element.find('input:text.form-autocomplete').each(function()
      {
        // Autocomplete fields add the value in a sibling hidden input
        // with the autocomplete id as the name
        var id = $(this).attr('id');
        $(this).siblings('input:hidden[name="' + id + '"]').val('');
      });
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

      $('#top-bar [id$=menu]').tooltip(
        {
          'placement': 'bottom'
        })
        .click(function ()
          {
            $(this).tooltip('hide');
          });

      // Listen to class changes in the top div to change aria-expanded
      // attribute in the child button when the dropdown is opened/closed.
      // Bootstrap doesn't trigger any event in those cases until v3.
      $('#top-bar [id$=menu]').attrchange({
        trackValues: true,
        callback: function(evnt) {
          if(evnt.attributeName == 'class') {
            if(evnt.newValue.search(/open/i) == -1) {
              $(this).find('button.top-item').attr('aria-expanded', 'false');
            }
            else {
              $(this).find('button.top-item').attr('aria-expanded', 'true');
            }
          }
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
   ****  Facets
   ****
   ****/

  $(document).ready(function()
    {
      var $facets = $('#facets');
      var $facet = $facets.find('.facet');

      $facets.on('click', '.facets-header a', function (e)
        {
          $(e.target).toggleClass('open');
          $facets.find('.content').toggle();
        });

      $facet.on('click', '.facet-header a', function (e)
        {
          e.preventDefault();

          $(e.target).parents('.facet').toggleClass('open');
          $(e.target).attr('aria-expanded', function (index, attr) {
            return attr == 'false' ? 'true' : 'false';
          });
        });

      // Open first three facets
      $facet.slice(0, 3).filter(function(index, element)
        {
          return 0 < $(element).find('li').length;
        }).addClass('open').find('.facet-header a').attr('aria-expanded', 'true');
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
        var realm = radio.length ? radio.get(0).value : '';

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
            // If charCode is 40 then, in Chrome/IE, it's an open parenthesis
            if (e.charCode == 0)
            {
              e.preventDefault();
              this.move(1);
            }
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
      this.$form = this.$element.find('form[name="advanced-search-form"]');
      this.$toggle = this.$element.find('a.advanced-search-toggle');
      this.$reposFacet = this.$element.find("#\\#facet-repository").closest('section.facet');
      this.$reposFilter = this.$element.find('select[name="repos"]');
      this.$collectionFilter = this.$element.find('input[name="collection"]');
      this.$dateRangeHelpIcon = this.$element.find('a.date-range-help-icon');

      this.init();
      this.listen();
    };

  AdvancedSearch.prototype = {

    constructor: AdvancedSearch,

    init: function()
    {
      // Hide last criteria if more than once
      if (1 < this.$form.find('.criterion').length)
      {
        this.$form.find('.criterion:last').remove();
      }

      this.checkReposFilter();

      // Initialize datepickers
      var opts = {
        changeYear: true,
        changeMonth: true,
        yearRange: '-100:+100',
        dateFormat: 'yy-mm-dd',
        defaultDate: new Date(),
        constrainInput: false,
        beforeShow: function (input, instance) {
          var top  = $(this).offset().top + $(this).outerHeight();
          setTimeout(function() {
            instance.dpDiv.css({
              'top' : top,
            });
          }, 1);
        }
      };

      // Don't change user input value when enter is pressed with datepicker
      // It must be added before the datepicker is initialized
      $('#startDate, #endDate').bind('keydown', function (event) {
        if (event.which == 13) {
          var e = jQuery.Event('keydown');
          e.which = 9;
          e.keyCode = 9;
          $(this).trigger(e);

          return false;
        }
      }).datepicker(opts);
    },

    listen: function()
    {
      this.$form
        .on('click', '.add-new-criteria .dropdown-menu a', $.proxy(this.addCriterion, this))
        .on('click', 'input.reset', $.proxy(this.reset, this))
        .on('click', 'a.delete-criterion', $.proxy(this.deleteCriterion, this))
        .on('submit', $.proxy(this.submit, this));

      this.$toggle.on('click', $.proxy(this.toggle, this));
      this.$collectionFilter.on('change', $.proxy(this.checkReposFilter, this));
      this.$dateRangeHelpIcon.on('click', $.proxy(this.toggleDateRangeHelp, this));
    },

    checkReposFilter: function (event)
    {
      // Disable repository filter and facet if top-level description selected
      if (this.$reposFilter.length && this.$collectionFilter.val() != '')
      {
        this.$reposFilter.attr("disabled", "disabled");
        this.$reposFilter.val('');
        if (this.$reposFacet.length)
        {
          this.$reposFacet.hide();
        }
      }
      else if (this.$reposFilter.length && this.$collectionFilter.val() == '')
      {
        this.$reposFilter.removeAttr('disabled');
        if (this.$reposFacet.length)
        {
          this.$reposFacet.show();
        }
      }
    },

    submit: function (event)
    {
      // Disable empty fields and first operator in criteria
      this.$form.find(':input[value=""]').attr("disabled", "disabled");
      this.$form.find('select[name="so0"]').attr("disabled", "disabled");

      // Fix only year dates on form submit
      var sd = this.$form.find('#startDate');
      if (/^\d{4}$/.test(sd.val()))
      {
        sd.val(sd.val() + '-01-01');
      }
      var ed = this.$form.find('#endDate');
      if (/^\d{4}$/.test(ed.val()))
      {
        ed.val(ed.val() + '-12-31');
      }
    },

    reset: function (event)
    {
      window.location.replace(this.$form.attr('action') + '?showAdvanced=1&topLod=0');
    },

    addCriterion: function (event)
    {
      event.preventDefault();

      this
        .cloneLastCriterion()
        .insertAfter(this.$form.find('.criterion:last')).show()
        .find('select.boolean').val(event.target.id.replace('add-criterion-', '')).end()
        .find('input').first().focus();
    },

    cloneLastCriterion: function()
    {
      var $clone = this.$form.find('.criterion:last').clone();

      var nextNumber = parseInt($clone.find('input:first').attr('name').match(/\d+/).shift()) + 1;

      $clone.find('input, select').each(function (index, element)
      {
        var name = this.getAttribute('name').replace(/[\d+]/, nextNumber);
        this.setAttribute('name', name);
      });

      clearFormFields($clone);

      return $clone;
    },

    deleteCriterion: function (event)
    {
      event.preventDefault();

      var $criterion = $(event.target.closest('.criterion'));
      var targetNumber = parseInt($criterion.find('input:first').attr('name').match(/\d+/).shift());

      // First criterion without siblings, just clear that criterion
      if (targetNumber == 0 && this.$form.find('.criterion').length == 1)
      {
        clearFormFields($criterion);
        return;
      }

      // Otherwise update next siblings input and select names
      $criterion.nextAll('.criterion').each(function ()
        {
          var $this = $(this);
          var number = parseInt($this.find('input:first').attr('name').match(/\d+/).shift());
          $this.find('input, select').each(function (index, element)
          {
            var name = this.getAttribute('name').replace(/[\d+]/, number - 1);
            this.setAttribute('name', name);
          });
        });

      // Then delete criterion
      $criterion.remove();
    },

    toggle: function (e)
    {
      e.preventDefault();

      if(this.$toggle.toggleClass('open').hasClass('open'))
      {
        this.$toggle.attr('aria-expanded', true);
      }
      else
      {
        this.$toggle.attr('aria-expanded', false);
      }

      $('.advanced-search').toggle(400);
    },

    toggleDateRangeHelp: function (e)
    {
      e.preventDefault();

      if(this.$dateRangeHelpIcon.toggleClass('open').hasClass('open'))
      {
        this.$dateRangeHelpIcon.attr('aria-expanded', true);
      }
      else
      {
        this.$dateRangeHelpIcon.attr('aria-expanded', false);
      }

      $('.date-range-help').toggle(400);
    }
  };

  $(function ()
    {
      // Find search for if on an appropriate page
      var $advancedSearch = $('body.informationobject.browse,body.search.descriptionUpdates');
      if (0 < $advancedSearch.length)
      {
        new AdvancedSearch($advancedSearch.get(0));
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
              .val($this.data('subquery-field-value'));
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
      if (!s.length)
      {
        return;
      }

      var pos = s.position();
      $(window).scroll(function()
        {
          var windowpos = $(window).scrollTop();
          if (windowpos >= pos.top)
          {
            s.addClass("stick");
          }
          else
          {
            s.removeClass("stick");
          }
        });
    });

})(window.jQuery);
