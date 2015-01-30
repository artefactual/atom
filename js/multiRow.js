(function ($)
  {
    Qubit.multiRow = Qubit.multiRow || {};

    Qubit.multiRow.addNewRow = function (sender)
      {
        var table = $(sender).parents('table:first');
        var lastRow = table.find('tbody tr:last');
        var newRow = lastRow.clone();

        // Get the last row number (e.g.: foo[0][bar])
        var lastRowNumber = parseInt(lastRow.find("select, input, textarea").filter(":first").attr("name").match(/\d+/).shift());

        // Replace yui div with form-autocomplete elements
        newRow.find('.yui-ac').each(function()
          {
            var name = $(this).children('input[name]:first').attr("name");
            var id = $(this).children('input[id]:first').attr("id");
            var inputAdd = $(this).children('input[class=add]')[0];
            var inputList = $(this).children('input[class=list]')[0];
            $(this).replaceWith('<select name="' + name + '" class="form-autocomplete" id="' + id + '"></select>'
              + inputAdd.outerHTML
              + inputList.outerHTML);
          });

        // Iterate over each input, select and textarea elements
        newRow.find('input, select, textarea').each(function(i)
          {
            // Input values are removed except hidden ones (not in childsTable)
            if ($(this).is('input, textarea') && ($(this).attr("type") != "hidden" || table.attr("id") == "childsTable"))
            {
              $(this).val('');
            }
            // Select index is preserved
            else if ($(this).is('select'))
            {
              var oldSelect = lastRow.find('input, select, textarea').eq(i);
              var selectedIndex = oldSelect[0].selectedIndex;

              $(this)[0].selectedIndex = selectedIndex;
            }

            // Increment row number
            if ($(this).attr('name'))
            {
              var newName = $(this).attr('name').replace(/\[\d+\]/, '[' + (lastRowNumber + 1) + ']');
              $(this).attr('name', newName);
            }
            if ($(this).attr('id'))
            {
              var newId = $(this).attr('id').replace(/\_\d+\_/, '_' + (lastRowNumber + 1) + '_');
              $(this).attr('id', newId);
            }
          });

        // Alternate row style
        if (newRow.hasClass('even'))
        {
          newRow.removeClass('even').addClass('odd');
        }
        else if (newRow.hasClass('odd'))
        {
          newRow.removeClass('odd').addClass('even');
        }

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

          // Focus first field and trigger event to load functions
          .first().children("select, input, textarea").focus().trigger('loadFunctions');
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

                // deleting element sometimes causes focusout not to fire in Firefox
                $this.trigger('focusout');

                table = $this.closest('table');
                rows = table.find('tbody tr');
                objectRows = table.find('tr[class^="even related_obj_"], tr[class^="odd related_obj_"]');
                row = $this.closest('tr');

                // Only remove row if it isn't the last one without a related object. Adding a new row based
                // on a related object row may creates id duplication or not form fields in the new row
                if (1 < rows.length - objectRows.length || $(row).attr("class").indexOf('related_obj_') >= 0)
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

                  var rowNumber = parseInt(row.find("select, input, textarea").filter(":first").attr("name").match(/\d+/).shift());

                  rowNumber--;

                  row.nextAll().each(function()
                    {
                      rowNumber++;

                      $(this).find('input, select, textarea').each(function()
                        {
                          if ($(this).attr('name'))
                          {
                            var newName = $(this).attr('name').replace(/\[\d+\]/, '[' + rowNumber + ']');
                            $(this).attr('name', newName);
                          }
                          if ($(this).attr('id'))
                          {
                            var newId = $(this).attr('id').replace(/\_\d+\_/, '_' + rowNumber + '_');
                            $(this).attr('id', newId);
                          }
                        });

                      // Alternate row style
                      if ($(this).hasClass('even'))
                      {
                        $(this).removeClass('even').addClass('odd');
                      }
                      else if ($(this).hasClass('odd'))
                      {
                        $(this).removeClass('odd').addClass('even');
                      }
                    });

                  row.find('div').hide('normal', function()
                  {
                    row.remove();
                  });
                }
                else
                {
                  row.find('input, select, textarea').each(function()
                    {
                      // Input values are removed except hidden ones
                      if ($(this).is('input, textarea') && $(this).attr("type") != "hidden")
                      {
                        $(this).val('');
                      }
                      else if ($(this).is('select'))
                      {
                        $(this)[0].selectedIndex = 0;
                      }
                    });
                }
              });
        }};
  })(jQuery);
