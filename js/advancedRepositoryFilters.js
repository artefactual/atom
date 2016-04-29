(function ($) {
  'use strict';

  $(function()
    {
      var $filtersToggle = $('#toggleAdvancedFilters');

      $filtersToggle.click(function() {
        var $filtersSection = $('#advancedRepositoryFilters');

        // Animate the toggle button
        if ($filtersSection.css('display') == 'block')
        {
          $filtersToggle.removeClass('fa-angle-double-up');
          $filtersToggle.addClass('fa-angle-double-down');
        }
        else if ($filtersSection.css('display') == 'none')
        {
          $filtersToggle.removeClass('fa-angle-double-down');
          $filtersToggle.addClass('fa-angle-double-up');
        }

        $filtersSection.slideToggle(200);
      });
    });
})(jQuery);
