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

          // Don't change user input value when enter is pressed
          jQuery('#sd, #ed').bind('keydown', function (event) {
            if (event.which == 13) {
              var e = jQuery.Event('keydown');
              e.which = 9;
              e.keyCode = 9;
              $(this).trigger(e);

              return false;
            }
          }).datepicker(opts);

          // Fix only year dates on form submit
          jQuery('form').bind('submit', function (event) {
            var sd = $(this).find('#sd');
            if (/^\d{4}$/.test(sd.val())) {
              sd.val(sd.val() + '-01-01');
            }
            var ed = $(this).find('#ed');
            if (/^\d{4}$/.test(ed.val())) {
              ed.val(ed.val() + '-12-31');
            }
          });
        }};
  })(jQuery);
