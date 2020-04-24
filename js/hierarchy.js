"use strict";

(function ($) {

  $(loadTreeView);

  function startActivity()
  {
    $('#fullwidth-treeview-activity-indicator').show();
  }

  function endActivity()
  {
    $('#fullwidth-treeview-activity-indicator').hide();
  }

  function loadTreeView ()
  {
    var url  = 'Data';
    var detailUrl = '/informationobject/fullWidthTreeView';
    var $fwTreeView = $('<div id="fullwidth-treeview"></div>');
    var $fwTreeViewRow = $('<div id="fullwidth-treeview-row"></div>');
    var $mainHeader = $('#main-column');
    var $moreButton = $('#fullwidth-treeview-more-button');
    var $resetButton = $('#fullwidth-treeview-reset-button');
    var $treeViewConfig = $('#fullwidth-treeview-configuration');
    var itemsPerPage = $treeViewConfig.data('items-per-page');

    var pager = new Qubit.TreeviewPager(itemsPerPage, $fwTreeView, window.location.pathname + url);

    // Show always reset button in hierarchy browse page
    $resetButton.show();

    // True until a node is selected manually (not by state restoration)
    var refresh = true;
    startActivity();

    // Add tree-view div after main header (sizing to fill vertical space)
    var height = $(document).height() - $('#main-column').offset().top - 50;
    $mainHeader.after(
      $fwTreeViewRow
        .append($fwTreeView)
        .animate({height: height}, 500)
    );

    // Declare jsTree options
    var options = {
      'plugins': ['state', 'types'],
      'types': treeviewTypes,
      'core': {
        'data': {
          'url': function (node) {
            if (node.id === '#')
            {
              // Get first page of results
              var queryString = "?nodeLimit=" + (pager.getSkip() + pager.getLimit());
              return window.location.pathname + url + queryString;
            }
            else
            {
              return node.a_attr.href + detailUrl;
            }
          },
          'dataFilter': function (response) {
            // Data from the initial request for hierarchy data contains
            // additional data relating to paging so we need to parse to
            // differentiate it.
            var data = JSON.parse(response);

            if (typeof data.nodes === "undefined") {
              // Data is an array of jsTree node definitions
              return JSON.stringify(data);
            } else {
              // Data includes both nodes and total.
              // Workaround to only update the pager's total on the first load.
              // This data filter is used in all responses and it can't be used
              // to determine the node from where the request was triggered.
              // On the first load the total will be updated and, if that total
              // is still 0 after the first load, this will never be reached.
              if (pager.total === 0)
              {
                pager.setTotal(data.total);
              }

              return JSON.stringify(data.nodes);
            }
          },
          'data': function (node) {
            return node.id === '#' ?
              {} :
              {'firstLoad': false, 'referenceCode': node.original.referenceCode};
          }
        },
        'check_callback': function (operation, node, node_parent, node_position, more) {
          // Restrict possible client-side manipulation of tree
          return operation === 'deselect_all' || operation === 'create_node';
        }
      }
    };

    /**
     *
     * Listeners
     *
     */

    // When tree ready, add additional nodes if the user has paged to them
    var readyListener = function ()
    {
        // Update the "more" link, restore the state, and indicate that page
        // has finished refreshing
        $fwTreeView.jstree(true).restore_state();
        pager.updateMoreLink($moreButton);
        refresh = false;
        endActivity();
    };

    // On node selection: change to the informationobject's page
    var selectNodeListener = function (e, data)
    {
      // If page has finished refreshing, deselect all nodes in case state has
      // restored selection
      if (!refresh)
      {
        $fwTreeView.jstree(true).deselect_all();
        $fwTreeView.jstree(true).save_state();
        window.location = data.node.a_attr.href;
      }
    };

    // Initialize jstree with options and listeners
    $fwTreeView
      .jstree(options)
      .bind('ready.jstree', readyListener)
      .bind('select_node.jstree', selectNodeListener);

    // Clicking "more" will add next page of results to tree
    $moreButton.click(function() {
      pager.next();
      startActivity();
      pager.getAndAppendNodes(function() {
        // Queue is empty so update paging link
        endActivity();
        pager.updateMoreLink($moreButton);
      });
    });

    // Clicking reset link will reset paging and tree state
    $resetButton.click(function()
    {
      pager.reset($moreButton);
    });
  }
})(jQuery);
