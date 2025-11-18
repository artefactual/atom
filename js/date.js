(($) => {
  "use strict";

  $(() => {
    $(document).on("change", ".date input[id$=date]", function () {
      var $this = $(this);
      var $row = $this.closest(".date");
      var $start = $row.find("[id$=startDate]");
      var $end = $row.find("[id$=endDate]");

      if (!$start.length || !$end.length) {
        return;
      }

      var min = [];
      var max = [];
      var matches = $this
        .val()
        .match(
          /\d+(?:[-/]0*(?:1[0-2]|\d)(?:[-/]0*(?:3[01]|[12]?\d))?(?!\d))?/g
        );

      if (matches) {
        $.each(matches, function (index) {
          var matches = $.map(this.match(/\d+/g), (elem) => elem - 0);

          if (0 === index) {
            min = max = matches;

            return;
          }

          $.each(min, function (index) {
            if (
              (this < matches[index] &&
                (0 !== index || 31 < this || 32 > matches[index])) ||
              (0 === index && 31 < this && 32 > matches[index])
            ) {
              return false;
            }

            if (this != matches[index]) {
              min = matches;
            }
          });

          $.each(max, function (index) {
            if (this > matches[index]) {
              return false;
            }

            if (this != matches[index]) {
              max = matches;
            }
          });
        });
      }

      $start.val(min.join("-"));
      $end.val(max.join("-"));
    });
  });
})(jQuery);
