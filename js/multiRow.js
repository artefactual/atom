// $Id: multiRow.js 10016 2011-10-11 18:02:25Z sevein $

(function ($)
  {
    Qubit.multiRow = Qubit.multiRow || {};

    Qubit.multiRow.addNewRow = function (sender)
      {
        var table = $(sender).parents('table:first');
        var lastRow = table.find('tbody tr:last');
        var newRow = lastRow.clone();

        // Get the last row number (e.g.: foo[0][bar])
        var lastRowNumber = parseInt(lastRow.find("select, input").filter(":first").attr("name").match(/\d+/).shift());

        // Iterate over each input and select elements
        newRow.find('input, select').each(function(i)
          {
            // Input values are removed
            if ($(this).is('input'))
            {
              $(this).val('');
            }
            // Select index is preserved
            else if ($(this).is('select'))
            {
              var oldSelect = lastRow.find('input, select').eq(i);
              var selectedIndex = oldSelect[0].selectedIndex;

              $(this)[0].selectedIndex = selectedIndex;
            }

            // Increment row number
            var newName = $(this).attr('name').replace(/\[\d+\]/, '[' + (lastRowNumber + 1) + ']');
            $(this).attr('name', newName);
          });

        // Iterate over each cell
        newRow.find('td').each(function(i)
          {
            // Wrap cell with div animateNicely for jQuery.show effect
            if (0 == $(this).find('div.animateNicely').length)
            {
              $(this).wrapInner('<div class="animateNicely"></div>');
            }

            // Hide the div
            $(this).children().hide();
          });

        table.children('tbody')

          // Append the row to body
          .append(newRow)

          // Show effect
          .find('tr:last div.animateNicely').show('normal')

          // Focus first field
          .first().children("input").focus();
      }

    /**
     * On page load, "multiRow" tables are prepared to add as many rows as users want
     */
    Drupal.behaviors.multiRow = {
      attach: function (context)
        {
          var tables = $('table.multiRow');

          // Add delete button to existing rows
          $('thead tr:first', tables).append('<th>&nbsp;</th>');
          $('tbody tr', tables).append('<td><button name="delete" class="delete-small" /></td>');

          tables
          
            // Add tfoot new row button
            // TODO: use append + live or delegate, embed addNewRow
            .each(function()
              {
                $('<tfoot><tr><td colspan=' + ($(this).find('tbody tr:first td').length + 1) + '><a href="#" onclick="Qubit.multiRow.addNewRow(this); return false;">Add new</a></td></tr></tfoot>').appendTo(this);
              })

            // If user press enter, add new row
            .find('input, select').live('keypress', function(event)
              {
                if (event.keyCode == 13 || event.charCode == 13)
                {
                  var table = $(this).parents('table:first');

                  if (0 == table.find(':animated').length)
                  {
                    table.find('tfoot a').trigger('click');
                  }

                  return false;
                }
              })

            .end().find('.delete-small').live('click', function(event)
              {
                event.preventDefault();

                $this = $(this);
                table = $this.closest('table');
                rows = table.find('tbody tr');
                row = $this.closest('tr');

                if (1 < rows.length)
                {
                  if (1 > row.children('div.animateNicely').length)
                  { 
                    row.find('td').each(function(i)
                      {
                        if (0 == $(this).find('div.animateNicely').length)
                        {
                          $(this).wrapInner('<div class="animateNicely"></div>');
                        }
                      });
                  }

                  row.find('div').hide('normal', function()
                    {
                      row.remove();
                    });

                  var rowNumber = parseInt(row.find("select, input").filter(":first").attr("name").match(/\d+/).shift());

                  rowNumber--;

                  row.nextAll().each(function()
                    {
                      rowNumber++;
                      $(this).find('input, select').each(function()
                        {
                          var newName = $(this).attr('name').replace(/\[\d+\]/, '[' + rowNumber + ']');
                          $(this).attr('name', newName);
                        });
                    });
                }
                else
                {
                  row.find('input, select').each(function()
                    {
                      $(this).val('');

                      if ($(this).is('select'))
                      {
                        $(this)[0].selectedIndex = 0;
                      }
                    });
                }
              });
        }};
  })(jQuery);
