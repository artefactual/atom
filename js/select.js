(function ($)
  {
    Drupal.behaviors.select = {
      select: function (option, name, $ul)
        {
          // Disable <option>
          $(option).attr('disabled', 'disabled');

          // Make <li> of hidden <input> with <option> value, and <span> with
          // <option> HTML contents
          $('<li><input name="' + name + '" type="hidden" value="' + $(option).val() + '"/><span>' + $(option).html() + '</span></li>')
            .click(function ()
              {
                // On click, remove <li> and enable <option>
                $(this).hide('fast', function ()
                  {
                    $(this).remove();

                    // Toggle <ul> based on children length
                    $ul.toggle(0 < $ul.children().length);
                  });

                $(option).removeAttr('disabled');
              })
            .appendTo($ul.show());
        },
      attach: function (context)
        {
          $('select[multiple]', context).each(function ()
            {
              // Share <select> name with nested scopes
              var name = $(this).attr('name');

              // Make <ul> of selected <option>s
              var $ul = $('<ul/>').hide().insertBefore(this);

              $('option:selected', this).each(function ()
                {
                  // Disable <option> and make <li>
                  Drupal.behaviors.select.select(this, name, $ul);
                });

              // Add blank option for clearing <select>
              var blank = $('<option/>')
                .attr('selected', 'selected')
                .prependTo(this)[0];

              $(this)

                // Change multiple <select> to single <select>
                .removeAttr('multiple')

                // Remove @name to avoid submitting single <select> along with
                // selected options
                .removeAttr('name')

                .bind('blur click keydown', function (event)
                  {
                    if ($(this).val() && ('keydown' != event.type || 9 == event.keyCode))
                    {
                      // Cancel default action so as not to loose focus
                      if ('keydown' == event.type)
                      {
                        event.preventDefault();
                      }

                      // Disable <option> and make <li>
                      Drupal.behaviors.select.select($('option:selected', this)[0], name, $ul);

                      // Clear <select>
                      $(blank).attr('selected', 'selected');
                    }
                  });
            });
        } };
  })(jQuery);
