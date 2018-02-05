"use strict";

(function ($) {

  $(loadTreeView);

  var pager = new Qubit.Pager(50);

  function startActivity()
  {
    $('#fullwidth-treeview-activity-indicator').show();
  }

  function endActivity()
  {
    $('#fullwidth-treeview-activity-indicator').hide();
  }

  function getAndAppendNodes(treeEl, url, skip, limit, cb)
  {
    // Assemble query and creation queue
    var queryString = "?skip=" + skip + "&nodeLimit=" + limit;
    var pagedUrl = window.location.pathname + url + queryString;
    var createQueue = [];

    // Get and append additional nodes
    startActivity();
    $.ajax({
      url: pagedUrl,
      success: function(results) {
        // Add nodes to creation queue
        results.nodes.forEach(function(node) {
          createQueue.push(node);
        });

        var next = function()
        {
          if (createQueue.length)
          {
            // Queue isn't empty: create node
            var node = createQueue.shift();
            treeEl.jstree(true).create_node("#", node, "last", next);
          }
          else
          {
            // Queue is empty so excute cleanup logic
            endActivity();
            cb();
          };
        };

        next();
      }
    });
  }

  function loadTreeView ()
  {
    var url  = 'Data';
    var detailUrl = '/informationobject/fullWidthTreeView';
    var $fwTreeView = $('<div id="fullwidth-treeview"></div>');
    var $fwTreeViewRow = $('<div id="fullwidth-treeview-row"></div>');
    var $mainHeader = $('#main-column');
    var $moreButton = $('#fullwidth-treeview-more-button');

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

    // Update count of remaining nodes, etc.
    function updateMoreLink()
    {
      var moreLabel = $moreButton.data('label');

      if (pager.getRemaining() > 0)
      {
        // Update count shown in paging button
        $moreButton.show();
        $('#fullwidth-treeview-more-button').val(moreLabel.replace('%1%', pager.getRemaining()));
      }
      else
      {
        // Hide paging button
        $moreButton.hide();
      }

      // Scroll to last item in tree
      if ($('li.jstree-node:last').length)
      {
        $('li.jstree-node:last')[0].scrollIntoView(true);
      }
    }

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
              // Data includes both nodes and the total number of available nodes
              pager.setTotal(data.total);
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
        updateMoreLink();
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
      getAndAppendNodes($fwTreeView, url, pager.getSkip(), pager.getLimit(), function() {
        // Queue is empty so update paging link
        updateMoreLink();
      });
    });

    // Clicking reset link will reset paging and tree state
    $('#fullwidth-treeview-reset-button').click(function()
    {
      pager.setSkip(0);

      // Only reset tree if it already exists
      if ($fwTreeView.jstree(true) !== false)
      {
        $fwTreeView.jstree(true).clear_state();
        $fwTreeView.jstree(true).refresh(true, true);
      }

      // Update paging button and scroll treeview window to first node
      updateMoreLink();
      $('li.jstree-node:first')[0].scrollIntoView(true);
    });
  }
})(jQuery);
