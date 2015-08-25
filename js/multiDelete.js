(function ($)
  {
    /**
     * On page load, replace "multiDelete" checkboxes with delete icons
     */
    Drupal.behaviors.multiDelete = {
      attach: function (context)
        {
          $('.multiDelete', context)
            .after(function ()
              {
                var $input = $(this);

                return $('<button class="delete-small" type="button"/>').click(function (event)
                  {
                    event.stopPropagation();

                    $input.attr('checked', 'checked');

                    // Hide element
                    var $parentRows = $(this).closest('tr');

                    // Add "animateNicely" <div/> to each <td/> to make "hide"
                    // animation play nicely
                    $('td:not(:has(.animateNicely))', $parentRows)

                      // Add a &nbsp; because .hide() doesn't seem to operate
                      // on <div/>s that contain only whitespace
                      .append('&nbsp;')

                      .wrapInner('<div class="animateNicely"/>');

                    $('div:visible', $parentRows).hide('normal', function ()
                      {
                        $parentRows.hide();
                      });
                  });
              })

            // Make sure that .after() returns an input element.
            // It returns document (which is not compatible
            // with hide()) if $('.multiDelete').lenght is zero
            .filter('input').hide();
        } }
  })(jQuery);
