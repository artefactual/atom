(function ($)
  {
    $(document).ready(function() {
      // Access to clipboard functionality
      var clipboard = $('#clipboard-menu').data('clipboard');
      if (typeof clipboard === 'undefined')
      {
        return;
      }

      var $area = $('#clipboardButtonNode');
      var $buttons = $area.find('button');

      $area.find('.all, .none').on('click', function (event) {
        event.preventDefault();

        // Generator of toggle function returning deferred objects so we can
        // put them in a queue and execute sequentially.
        function getToggleFn(button) {
          return function() {
            return clipboard.toggle(false, { 'target': button })
          }
        }

        // Are we selecting or unselecting all?
        var select = $(this).hasClass('all');

        // Our queue of toggle functions
        var queue = [];
        $buttons.each(function(index) {
          var $button = $buttons.eq(index);

          if (select === $button.hasClass('added'))
          {
            return;
          }

          queue.push(getToggleFn($button.get()))
        });

        // Execute functions sequentially
        var d = $.Deferred().resolve();
        while (queue.length > 0) {
          d = d.then(queue.shift());
        }
      });
    });
  }
)(jQuery);
