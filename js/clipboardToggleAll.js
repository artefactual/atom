(function ($)
  {
    // Toggle buttons
    var clickClipButton = function (node, selectAll) {
      var $button = $(node);

      // We only want to click the button when needed
      if (selectAll === $button.hasClass('added'))
      {
        return;
      }

      $button.click();
    };

    // Convenience wrapper in jQuery
    $.fn.clickClipButton = function(clicked) {
      this.each(function () {
        clickClipButton.apply(null, [this, clicked]);
      });
    };

    // get all affected clipboard buttons and select all or none based on
    // item clicked.
    $(document).ready(function() {

      var $area = $('#clipboardButtonNode');
      var $buttons = $area.find('button');

      $area.find('.all').on('click', function (event) {
        event.preventDefault();
        $buttons.clickClipButton(true);
      });

      $area.find('.none').on('click', function (event) {
        event.preventDefault();
        $buttons.clickClipButton(false);
      });
    });
  }
)(jQuery);
