"use strict";

(function ($) {

  $(loadTreeView);

  function makeFullTreeviewCollapsible ($treeViewConfig, $mainHeader, $fwTreeViewRow)
  {
    var $wrapper = $('<section class="full-treeview-section"></section>');
    var $toggleButton = $('<a href="#" class="fullview-treeview-toggle"></a>');

    // Adjust bottom margins
    var bottomMargin = $fwTreeViewRow.css('margin-bottom');
    $fwTreeViewRow.css('margin-bottom', '0px');
    $wrapper.css('margin-bottom', bottomMargin);

    // Set toggle button text and add to wrapper
    $toggleButton.text($treeViewConfig.data('closed-text'));
    $toggleButton.appendTo($wrapper);

    // Add wrapper to the DOM then hide the treeview and add it to the wrapper
    $mainHeader.after($wrapper);
    $fwTreeViewRow.hide();
    $fwTreeViewRow.appendTo($wrapper);

    // Activate toggle button
    $toggleButton.click(function() {
      // Determine appropriate toggle button text
      var toggleText = $treeViewConfig.data('opened-text');

      if ($fwTreeViewRow.css('display') != 'none')
      {
        toggleText = $treeViewConfig.data('closed-text');
      }

      // Toggle treeview and set toggle button text
      $fwTreeViewRow.toggle(400);
      $toggleButton.text(toggleText);
    });
  }

  function loadTreeView ()
  {
    var $treeViewConfig = $('#fullwidth-treeview-configuration');
    var treeViewCollapseEnabled = $treeViewConfig.data('collapse-enabled') == 'yes';
    var collectionUrl = $treeViewConfig.data('collection-url');
    var itemsPerPage = $treeViewConfig.data('items-per-page');
    var pathToApi  = '/informationobject/fullWidthTreeView';
    var $fwTreeView = $('<div id="fullwidth-treeview"></div>');
    var $fwTreeViewRow = $('<div id="fullwidth-treeview-row"></div>');
    var $mainHeader = $('#main-column h1').first();
    var $moreButton = $('#fullwidth-treeview-more-button');
    var $resetButton = $('#fullwidth-treeview-reset-button');
    var pager = new Qubit.TreeviewPager(itemsPerPage, $fwTreeView, collectionUrl + pathToApi);

    // Add tree-view divs after main header, animate and allow resize
    $mainHeader.after(
      $fwTreeViewRow
        .append($fwTreeView)
        .animate({height: '200px'}, 500)
        .resizable({handles: 's'})
    );

    $mainHeader.before($resetButton);
    $mainHeader.before($moreButton);

    // Optionally wrap treeview in a collapsible container
    if (treeViewCollapseEnabled)
    {
      makeFullTreeviewCollapsible($treeViewConfig, $mainHeader, $fwTreeViewRow);
    }

    // Declare jsTree options
    var options = {
      'plugins': ['types', 'dnd'],
      'types': treeviewTypes,
      'dnd': {
        // Drag and drop configuration, disable:
        // - Node copy on drag
        // - Load nodes on hover while dragging
        // - Multiple node drag
        // - Root node drag
        'copy': false,
        'open_timeout': 0,
        'drag_selection': false,
        'is_draggable': function (nodes) {
          return nodes[0].parent !== '#';
        }
      },
      'core': {
        'data': {
          'url': function (node) {
            // Get results
            var queryString = "?nodeLimit=" + (pager.getSkip() + pager.getLimit());

            return node.id === '#' ?
              window.location.pathname.match("^[^;]*")[0] + pathToApi + queryString :
              node.a_attr.href + pathToApi;
          },
          'data': function (node) {
            return node.id === '#' ?
              {'firstLoad': true} :
              {'firstLoad': false, 'referenceCode': node.original.referenceCode};
          },

          'dataFilter': function (response) {
            // Data from the initial request for hierarchy data contains
            // additional data relating to paging so we need to parse to
            // differentiate it.
            var data = JSON.parse(response);

            // Note root node's href and set number of available items to page through
            if (pager.rootId == '#')
            {
              pager.rootId = data.nodes[0].id;
              pager.setTotal(data.nodes[0].total);
            }

            // Allow for both styles of nodes
            if (typeof data.nodes === "undefined") {
              // Data is an array of jsTree node definitions
              return JSON.stringify(data);
            } else {
              // Data includes both nodes and the total number of available nodes
              return JSON.stringify(data.nodes);
            }
          },
        },
        'check_callback': function (operation, node, node_parent, node_position, more) {
          // Operations allowed:
          // - Before and after drag and drop between siblings
          // - Move core operations (node drop event)
          return operation === 'create_node' ||
            (operation === 'move_node'
              && (more.core || (more.dnd
              && node.parent === more.ref.parent
              && more.pos !== 'i'))
            );
        }
      }
    };

    // Declare listeners
    // On ready: scroll to active node
    var readyListener = function ()
    {
      var $activeNode = $('li[selected_on_load="true"]')[0];

      pager.updateMoreLink($moreButton, $resetButton);

      // Override default scrolling
      if ($activeNode !== undefined)
      {
        $activeNode.scrollIntoView(true);
        $('body')[0].scrollIntoView(true);
      }
    };

    // On node selection: load the informationobject's page and insert the current page
    var selectNodeListener = function (e, data)
    {
      // Open node if possible
      data.instance.open_node(data.node);

      // When an element is clicked in the tree ... fetch it up
      // window.location = window.location.origin + data.node.a_attr.href
      var url = data.node.a_attr.href;
      $.get(url, function (response)
      {
        response = $(response);

        // Insert new content into page
        $('#main-column h1').first().replaceWith($(response.find('#main-column h1').first()));

        // Add empty breadcrumb section if current page has none, but response does
        if (!$('#main-column .breadcrumb').length && $(response.find('#main-column .breadcrumb').length))
        {
          var breadcrumbDestinationSelector = (treeViewCollapseEnabled)
            ? '.full-treeview-section'
            : '#fullwidth-treeview-row';

          $(breadcrumbDestinationSelector).after($('<section>', {class: 'breadcrumb'}));
        }
        $('#main-column .breadcrumb').replaceWith($(response.find('#main-column .breadcrumb')));

        // Replace description content
        $('#main-column .row').replaceWith($(response.find('#main-column .row')));

        // If translation links exist in the response page, create element, if necessary,
        // and replace translation links in the current page with them
        if (response.find('.translation-links').length && !$('.translation-links').length)
        {
          $('section.breadcrumb').after($('<div class="btn-group translation-links"></div>'));
        }
        $('.translation-links').replaceWith($(response.find('.translation-links')));

        // Replace error message
        $('#main-column > div.messages.error').remove();
        $('#main-column .breadcrumb').after($(response.find('#main-column > div.messages.error')));

        // Attach the Drupal Behaviour so blank.js does its thing
        Drupal.attachBehaviors(document)

        // Update clipboard buttons
        if (jQuery('#clipboard-menu').data('clipboard') !== undefined)
        {
          jQuery('#clipboard-menu').data('clipboard').updateAllButtons();
        }

        // Update the url, TODO save the state
        window.history.pushState(null, null, url);
      });
    };

    // On node hover: configure tooltip. A reminder is needed each time
    // a node is hovered to make it appear after node changes. It must
    // use the #fullwidth-treeview container to allow a higher
    // height than the node in multiple lines tooltips
    var hoverNodeListener = function (e, data)
    {
      $('a.jstree-anchor').tooltip({
        delay: 250,
        container: '#fullwidth-treeview'
      });
    };

    // On node open: remove tooltip after a node is selected, the 
    // node is reloaded and the first tooltip is never removed
    var openNodeListener = function (e, data)
    {
      $("#fullwidth-treeview .tooltip").remove();
    };

    // On node move: remove persistent tooltip and execute
    // Ajax request to update the hierarchy in the backend
    var moveNodeListener = function (e, data)
    {
      $("#fullwidth-treeview .tooltip").remove();

      // Avoid request if new and old positions are the same,
      // this can't be avoided in the check_callback function
      // because we don't have both positions in there
      if (data.old_position === data.position)
      {
        return;
      }

      var moveResponse = JSON.parse($.ajax({
        url: data.node.a_attr.href + '/informationobject/fullWidthTreeViewMove',
        type: 'POST',
        async: false,
        data: {
          'oldPosition': data.old_position,
          'newPosition': data.position
        }
      }).responseText);

      // Show alert with request result
      if (moveResponse.error)
      {
        $(
          '<div class="alert">' +
          '<button type="button" data-dismiss="alert" class="close">&times;</button>'
        )
        .append(moveResponse.error)
        .prependTo($('#wrapper.container'));

        // Reload treeview if failed
        data.instance.refresh();
      }
      else if (moveResponse.success)
      {
        $(
          '<div class="alert">' +
          '<button type="button" data-dismiss="alert" class="close">&times;</button>'
        )
        .append(moveResponse.success)
        .prependTo($('#wrapper.container'));
      }
    };

    // Initialize jstree with options and listeners
    $fwTreeView
      .jstree(options)
      .bind('ready.jstree', readyListener)
      .bind('select_node.jstree', selectNodeListener)
      .bind('hover_node.jstree', hoverNodeListener)
      .bind('open_node.jstree', openNodeListener)
      .bind('move_node.jstree', moveNodeListener);

    // Clicking "more" will add next page of results to tree
    $moreButton.click(function() {
      pager.next();
      pager.getAndAppendNodes(function() {
        // Queue is empty so update paging link
        pager.updateMoreLink($moreButton, $resetButton);
      });
    });

    // Clicking reset link will reset paging and tree state
    $('#fullwidth-treeview-reset-button').click(function()
    {
      pager.reset($moreButton, $resetButton);
    });

    // TODO restore window.history states
    $(window).bind('popstate', function() {});
  }
})(jQuery);
