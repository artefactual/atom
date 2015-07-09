(function ($)
  {
    Drupal.behaviors.dateRangeDatepicker = {
      attach: function (context)
        {
          var opts = {
            changeYear: true,
            changeMonth: true,
            yearRange: '-100:+100',
            dateFormat: 'yy-mm-dd',
            defaultDate: new Date(),
            constrainInput: false,
            beforeShow: function (input, instance) {
              var top  = $(this).offset().top + $(this).outerHeight();
              setTimeout(function() {
                instance.dpDiv.css({
                  'top' : top,
                });
              }, 1);
            }
          };

          jQuery('#sd, #ed').datepicker(opts);
        }};
  })(jQuery);
