(function ($)
  {
    window.description_focusin = function()
      {
        var $description = $('.description', this);
        var $sidebar = $('#sidebar-first');

        // Specific case for tooltips in YUI dialogs
        var $dialog = $(this).closest('div.yui-panel');
        if ($dialog.length)
        {
          var positionateDialog = function()
            {
              $description

                // Remove position relative to align with respect to the dialog
                .closest('.form-item').css('position', 'static').end()

                .addClass('description-dialog')

                .clone().prependTo($dialog)

                // Show tooltip
                .show();
            };

          positionateDialog();

          return true;
        }
        else
        {
          $description.show();

          $sidebar
            .css('position', 'relative')
            .prepend('<div class="description-overlay">&nbsp;</div>');
        }
      };

    window.description_focusout = function()
      {
        $('.description', this).hide();
        $('#sidebar-first').find('.description-overlay').remove();
        $('div.yui-panel > .description-dialog').remove();
      };

    $(document).ready(function()
      {
        // Menu
        $(document.body).bind('click', function (e)
          {
            $target = $(e.target);
            menuClicked = $target.hasClass('menu') && 'A' == $target.get(0).tagName;

            // Close menu if is clicked and is opened
            if (menuClicked && $target.parent().hasClass('open'))
            {
              $target.parent().removeClass('open');
            }
            else
            {
              $('li.menu').removeClass("open");

              if (menuClicked)
              {
                $target.parent().addClass('open');
              }
            }

            if (menuClicked)
            {
              return false;
            }
          });

        // Add close button to messages box
        $('<a class="close" href="#">&times;</a>')
          .click(function()
            {
              $(this).parent().remove();
            })
          .prependTo('.messages');

        // Double title, add class "small" to first one
        $('h1.label').parent('a').prev('h1').addClass('small');
        $('h1.label').prev('h1').addClass('small');

        // Extra edit button for view areas
        $('body.index #content > .section > .section').each(function()
          {
            $this = $(this);
            $first = $(this).children(':first');
            if ($first.is('a'))
            {
              $first.before($first.clone().addClass('editLink'));
              $this.css('position', 'relative');
            }
          });

        // Scroll, TODO
        /*
        if (window.location.hash.length)
        {
          window.scrollBy(0, headerHeight);
        }
        */

        // Search box behavior
        $('div.search').each(function()
          {
            var $sender = $(this);
            var $input = $sender.find('input[name=query], input[name=subquery]');
            var $submit = $sender.find('input:submit');

            // Capture search box title
            var text = $sender.find('h2, h3').text();
            if (!text)
            {
              text = $sender.find(':submit').val();
            }

            // Add title behavior to search input
            if (!$input
              .focus(function()
                {
                  if($(this).val() == text)
                  {
                    $(this).attr("value", "");
                  }
                })
              .blur(function()
                {
                  if (!$(this).val())
                  {
                    $(this).val(text);
                  }
                })
              .val())
            {
              $input.val(text);
            }

            $submit.click(function()
              {
                if ($input.val() == $(this).val())
                {
                  $input.focus();

                  return false;
                }
              });

          });

    });

  })(jQuery);
