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
          $filtersToggle.removeClass('icon-double-angle-up');
          $filtersToggle.addClass('icon-double-angle-down');
        }
        else if ($filtersSection.css('display') == 'none')
        {
          $filtersToggle.removeClass('icon-double-angle-down');
          $filtersToggle.addClass('icon-double-angle-up');
        }

        $filtersSection.slideToggle(200);
      });
    });
})(jQuery);
