(function($) {

  'use strict';

  $(() => {
    var $input =  $('#search-box-input');
    var $results = $('#search-box-results');
    var dropdown = new bootstrap.Dropdown($input);

    // Set up Bootstrap autocomplete:
    // - Force version 4 to avoid failing check in version 5.
    // - Reduce default throttling and ignore responses that
    //   come after the input is lower than 3 chars.
    // - Mixed with default Bootstrap 5 dropdown to improve
    //   behavior, accessibility and style.
    // - Avoid no results and results dropdown, and use custom
    //   search results dropdown body.
    $input.autoComplete({
      bootstrapVersion: '4',
      resolverSettings: {queryKey: 'query', requestThrottling: 250},
      noResultsText: '',
      events: {
        searchPost: (response, $element) => {
          if (response.length && $element.val().length >= 3) {
            $results.html(response);
            dropdown.show();
          } else {
            dropdown.hide();
            $results.html('');
          }
          return [];
        }
      }
    });

    // Hide dropdown when the input is lower than 3 chars.
    // Bootstrap autocomplete `typed` event is not triggered
    // on all changes to the input.
    $input.on('input', event => {
      if (event.target.value.length < 3) {
        dropdown.hide();
        $results.html('');
      }
    });

    // Prevent showing an empty dropdown
    $input.on('show.bs.dropdown', event => {
      if ($results.children().length == 0) {
        event.preventDefault();
      }
    });
  });

})(jQuery);
