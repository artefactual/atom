(function ($)
  {
    Drupal.behaviors.multiInput = {
      attach: function (context)
        {
          $('ul.multiInput > li', context).click(function (event)
            {
              if (this === event.target)
              {
                // On click, remove <li/>
                $(this).hide('fast', function ()
                  {
                    $(this).remove();
                  });
              }
            });

          $('input.multiInput', context).each(function ()
            {
              var index = 0;
              var name = this.name.replace('[new]', '');

              $(this)

                // Remove @name to avoid submitting value in the "new" <input/>
                .removeAttr('name')

                // Bind blur, click, or keydown events
                .bind('blur click keydown', function (event)
                  {
                    // Don't fire on keydown other than tab (9) or enter (13)
                    if ($(this).val()
                        && ('keydown' !== event.type
                          || 9 === event.keyCode
                          || 13 === event.keyCode))
                    {
                      // Cancel default action so as not to loose focus
                      if ('keydown' === event.type)
                      {
                        event.preventDefault();
                      }

                      var $ul = $(this).prev('ul.multiInput');
                      if (!$ul.length)
                      {
                        // Add <ul/> element, if it doesn't exist already (new
                        // object)
                        $ul = $('<ul class="multiInput"/>').insertBefore(this);
                      }

                      // Add input value to multiInput
                      var $li = $('<li><input type="text" name="' + name + '[new' + index++ + ']" value="' + this.value + '"/></li>')

                        // Bind click event to new list item
                        .click(function (event)
                          {
                            if (event.target === this)
                            {
                              // On click, remove <li/>
                              $(this).hide('fast', function ()
                                {
                                  $(this).remove();
                                });
                            }
                          })
                        .appendTo($ul);

                      // Clear <input/>
                      this.value = '';
                    }
                  });
            });
        } };
  })(jQuery);
