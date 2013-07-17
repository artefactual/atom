(function ($)
  {
    Drupal.behaviors.date = {
      attach: function (context)
        {
          function parse(value)
          {
            var min = [];
            var max = [];

            var matches = value.match(/\d+(?:[-/]0*(?:1[0-2]|\d)(?:[-/]0*(?:3[01]|[12]?\d))?(?!\d))?/g);
            if (matches)
            {
              jQuery.each(matches, function (index)
                {
                  var matches = jQuery.map(this.match(/\d+/g), function (elem)
                    {
                      return elem - 0;
                    });

                  if (0 === index)
                  {
                    min = max = matches;

                    return;
                  }

                  jQuery.each(min, function (index)
                    {
                      if (this < matches[index] && (0 !== index
                            || 31 < this || 32 > matches[index])
                          || 0 === index && 31 < this && 32 > matches[index])
                      {
                        return false;
                      }

                      if (this != matches[index])
                      {
                        min = matches;
                      }
                    });

                  jQuery.each(max, function (index)
                    {
                      if (this > matches[index])
                      {
                        return false;
                      }

                      if (this != matches[index])
                      {
                        max = matches;
                      }
                    });
                });
            }

            return [min.join('-'), max.join('-')];
          }

          function isValidDate(date)
          {
            if (Object.prototype.toString.call(date) !== "[object Date]")
            {
              return false;
            }

            return !isNaN(date.getTime());
          }

          $('.date', context).each(bindParse);

          // Use $(document).on('loadFunctions') to add function in new rows created with multiRow.js
          $(document).on('loadFunctions','.date', bindParse);

          // Use named function so it can be bound to events
          function bindParse()
            {
              var $start = $('[id$=startDate]', this);
              var $end = $('[id$=endDate]', this);

              var components = parse($('[id$=date]', this)
                .change(function ()
                  {
                    if (components[0] === $start.val() && components[1] === $end.val())
                    {
                      components = parse($(this).val());

                      $start.val(components[0]);
                      $end.val(components[1]);
                    }
                  })
                .val());
            }

          $('input.date-widget', context).each(function ()
            {
              var self = this;

              $(self)

                // Prepare the input field
                .css({'float': 'left', 'width': 'auto'})

                // Add calendar button
                .after('&nbsp;<button><img src="' + self.getAttribute('icon') + '" /></button>').next()

                // Bind next function to click event
                .click(function(event)
                {
                  event.preventDefault();

                  // If already exists, use it instead of a new one
                  if (self.calendar)
                  {
                    self.calendar.show();
                  }
                  else
                  {
                    // Create container element and add to the DOM
                    var container = $(document.createElement('div'))
                      .css({
                        'position': 'absolute',
                        'left': parseInt($(self).width() + 60) + 'px',
                        'z-index': 4})
                      .insertAfter(self);

                    self.calendar = new YAHOO.widget.Calendar(container.get(0), { close: true });

                    self.calendar.selectEvent.subscribe(function(type, args, obj)
                      {
                        pad = function(n) { return n < 10 ? '0' + n : n; };
                        self.value = args[0][0][0] + "-" + pad(args[0][0][1]) + "-" + pad(args[0][0][2]);
                        self.calendar.hide();
                      })

                    self.calendar.render();
                  }

                  var date = new Date(self.value);
                  if (isValidDate(date))
                  {
                    self.calendar.cfg.setProperty('pagedate', (date.getMonth() + 1) + "/" + date.getFullYear());
                    self.calendar.render();
                  }
                })
            });
        } };
  })(jQuery);
