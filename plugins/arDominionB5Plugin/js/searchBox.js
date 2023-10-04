(($) => {
  "use strict";

  $(() => {
    var $input = $("#search-box-input");
    if (!$input.length) {
      return;
    }
    var $searchboxDropdown = $("#search-box-dropdown");
    var dropdown = bootstrap.Dropdown.getOrCreateInstance($input);
    var $searchboxTemplate = $("#searchbox-options-template");

    let $searchOptions = $searchboxTemplate[0].content.cloneNode(true);
    $searchOptions.querySelector('div').id = 'search-options';
    $searchboxDropdown.html('<ul id="search-box-results"></ul>');
    $searchboxDropdown.append($searchOptions);
    let $results = $("#search-box-results");

    // Set up Bootstrap autocomplete:
    // - Force version 4 to avoid failing check in version 5.
    // - Mixed with default Bootstrap 5 dropdown to improve
    //   behavior, accessibility and style.
    // - Use custom Ajax request to add repos checkbox param.
    // - Ignore responses when the input is lower than 3 chars.
    // - Avoid no results and results dropdown, and use custom
    //   search results dropdown body.
    $input.autoComplete({
      bootstrapVersion: "4",
      noResultsText: "",
      events: {
        search: (query, callback, $element) => {
          var data = { query: query };
          var $repos = $('#search-box input[name="repos"]:checked');
          if ($repos.length && $repos.val()) {
            data.repos = $repos.val();
          }
          $.ajax($element.data("url"), { data: data }).done((res) =>
            callback(res)
          );
        },
        searchPost: (response, $element) => {
          if (response.length && $element.val().length >= 3) {
            //let $searchOptions = $searchboxTemplate[0].content.cloneNode(true);
            //$searchOptions.querySelector('div').id = 'search-options';
            //$results.html('<div class="dropdown-divider"></div>');
            $results.html(response);
            //dropdown.show();
          }
          //else {
            //let $searchOptions = $searchboxTemplate[0].content.cloneNode(true);
            //$searchOptions.querySelector('div').id = 'search-options';
            //$results.html($searchOptions);
            //$results.children()[0].attr('id', 'search-options');
          //}
          return [];
        },
      },
    });

    // Hide dropdown when the input is lower than 3 chars.
    // Bootstrap autocomplete `typed` event is not triggered
    // on all changes to the input.
    $input.on("input", (event) => {
      if (event.target.value.length < 3 && $results.children().length > 1) {
        $results.html('');
        //let $searchOptions = $searchboxTemplate[0].content.cloneNode(true);
        //$searchOptions.querySelector('div').id = 'search-options';
        //$results.html($searchOptions);
        //$results.children()[0].attr('id', 'search-options');
      }
    });

    $input.on("focus", (event) => {
      if (dropdown._isShown() === false) {
        dropdown.show();
      }
    });

    $input.on("hide.bs.dropdown", (event) => {
      if (document.activeElement === $input[0]) {
        event.preventDefault();
      }
    });

    // Prevent showing an empty dropdown
    $input.on("show.bs.dropdown", (event) => {
      if ($results.children().length === 0) {
        $results.html('');
        //let $searchOptions = $searchboxTemplate[0].content.cloneNode(true);
        //$searchOptions.querySelector('div').id = 'search-options';
        //$results.html($searchOptions);
        //$results.children()[0].attr('id', 'search-options');
      }
    });
  });
})(jQuery);
