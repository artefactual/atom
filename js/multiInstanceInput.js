(function ($) {
    // Add string method for removing trailing numeric characters
    String.prototype.trimTrailingDigits = function ()
      {
        return this.replace(/\d+$/, '');
      };

    /**
     * Javascript to spawn new <input> element as neeeded
     *
     * @param object thisElement dom <input> element to operate on
     * @return void
     */
    function multiInstanceInput(thisElement)
    {
      var elementName = thisElement.attr('name').toString();

      // Append array braces '[]' to element name (if not present)
      if (elementName.substr(elementName.length - 2) != '[]')
      {
        thisElement.attr('name', elementName += '[]');
      }

      // String to select all elements in this set
      var setSelector = "[name='" + elementName + "']";

      // Remove element if blank and it's not the only element in the set
      if (thisElement.val() == '' && $(setSelector).length > 1)
      {
        thisElement.hide('normal', function ()
          {
            thisElement.remove();
          });

        // NOTE: rest of script executes before thisElement.remove is called due to hide('slow') delay!
      }

      // If the last element is not blank, then add a new blank element
      var lastElement = $(setSelector).eq(($(setSelector).length) - 1);
      if (lastElement.val() != '') {
        // Clone lastElement and insert clone after lastElement (hidden)
        var newElement = thisElement.clone(true);
        newElement.val('');
        newElement.attr('name', elementName);
        newElement.css('display', 'none');

        // Insert after last element (take wrappers into account)
        newElement.insertAfter(lastElement);

        // Rebind onChange functions to newElement
        newElement.unbind();
        newElement.change(function ()
          {
            multiInstanceInput(newElement);
          });

        // Fancy fade-in effect (ooh, ahh!)
        newElement.show('normal', function ()
          {
            newElement.get(0).focus();
          });
      }

      // Build unique ids by appending array index (e.g. thisId0, thisId1, etc.)
      $(setSelector).each(function (i)
        {
          // Remove trailing digits from "id" attribute and append array index
          var idRoot = this.id.trimTrailingDigits();
          this.id = idRoot + i.toString();
        });
    }

    /**
     * Javascript to spawn new <input> element with wrapping table row (TR)
     *
     * @param object thisElement dom <input> element to operate on
     * @return void
     */
    function multiInstanceInputTr(thisElement)
    {
      var elementName = thisElement.attr('name').toString();
      var thisRow = thisElement.parents('tr').eq(0);

      // Append array braces '[]' to element name (if not present)
      if (elementName.substr(elementName.length - 2) != '[]')
      {
        thisElement.attr('name', elementName += '[]');
      }

      // String to select all elements in this set
      var setSelector = "[name='" + elementName + "']";

      // If the last element is not blank, then add a new blank element
      var lastElement = $(setSelector).eq(($(setSelector).length) - 1);
      if (lastElement.val() != '')
      {
        // Clone element
        var newElement = thisElement.clone();
        newElement.attr('value', '');
        newElement.attr('name', elementName);

        var newRow = thisRow.clone(true);
        newRow.find('input, textarea').eq(0).replaceWith(newElement);
        newRow.find('td').wrapInner('<div class="niceShow" style="display:none"></div>');

        // Insert after last row
        newRow.insertAfter(thisRow);

        // Fancy fade-in effect (ooh, ahh!)
        $('div.niceShow').show('normal', function ()
          {
            newRow.find('div.niceShow').each(function ()
              {
                $(this).replaceWith($(this).children());
              });

            // Focus on new element
            newElement.get(0).focus();
          });

        // Rebind onChange functions to newElement
        newElement.unbind();
        newElement.change(function ()
          {
            multiInstanceInputTr(newElement);
          });

        addDeleteIconTd(thisElement);
      }

      // Build unique ids by appending array index (e.g. thisId0, thisId1, etc.)
      $(setSelector).each(function (i)
        {
          // Remove trailing digits from "id" attribute and append array index
          var idRoot = this.id.trimTrailingDigits();
          this.id = idRoot + i.toString();
        });
    }

    function addDeleteIconTd(thisElement)
    {
      $('<button name="delete" class="delete-small"/>')
        .appendTo(thisElement.parents('tr').find('td:last'))
        .click(function ()
          {
            return removeTr(this);
          });
    }

    function removeTr(thisElement)
    {
      var thisRow = $(thisElement).parents('tr').eq(0);
      thisRow.find('td').wrapInner('<div class="niceHide"></div>');
      $('div.niceHide').hide('normal', function ()
        {
          thisRow.remove();
        });

      return false;
    }

    // Bind multiInstance functions to onChange event
    var fullOptionList = [];
    Drupal.behaviors.addMultiInstanceInput = {
      attach: function (context)
        {
          $('input.multiInstance').change(function ()
            {
              multiInstanceInput($(this));
            });

          $('textarea.multiInstance').change(function ()
            {
              multiInstanceInput($(this));
            });

          $('input.multiInstanceTr').change(function ()
            {
              multiInstanceInputTr($(this));
            });

          $('textarea.multiInstanceTr').change(function ()
            {
              multiInstanceInputTr($(this));
            });
        } };
  })(jQuery);
