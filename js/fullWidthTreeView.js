(function ($) {

    // Handle older browser using hash/anchor urls
    if (window.location.hash)
    {
      var url = window.location.hash.match("\/([^?]*)");
      if (url[1].length)
      {
        window.location = url[1];
        return;
      }
    }

  "use strict";

  $(loadTreeView);

  function loadTreeView ()
  {
    var url  = '/informationobject/fullWidthTreeView';
    var $fwTreeView = $('<div id="fullwidth-treeview"></div>');
    var $fwTreeViewRow = $('<div id="fullwidth-treeview-row"></div>');
    var $mainHeader = $('#main-column h1');

    // Add tree-view divs after main header, animate and allow resize
    $mainHeader.after(
      $fwTreeViewRow
        .append($fwTreeView)
        .animate({height: '200px'}, 500)
        .resizable({handles: 's'})
    );

    // Declare jsTree options
    var options = {
      'plugins': ['types'],
      'types': {
        'default':    {'icon': 'fa fa-folder-o'},
        'Item':       {'icon': 'fa fa-file-text-o'},
        'File':       {'icon': 'fa fa-file-text-o'},
        'Series':     {'icon': 'fa fa-folder-o'},
        'Subseries':  {'icon': 'fa fa-folder-o'},
        'subfonds':   {'icon': 'fa fa-folder-o'},
        'Sous-fonds': {'icon': 'fa fa-folder-o'},
        'Fonds':      {'icon': 'fa fa-archive'},
        'Collection': {'icon': 'fa fa-archive'}
      },
      'core': {
        'data': {
          'url': function (node) {
            return node.id === '#' ?
              window.location.pathname.match("^[^;]*")[0] + url :
              node.a_attr.href + url;
          },
          'data': function (node) {
            return node.id === '#' ?
              {'firstLoad': true} :
              {'firstLoad': false, 'referenceCode': node.original.referenceCode};
          }
        }
      }
    };

    // Declare listeners
    // On ready: scroll to active node
    var readyListener = function ()
    {
      var $activeNode = $('li[selected_on_load="true"]')[0];
      if ($activeNode !== undefined)
      {
        $activeNode.scrollIntoView(true);
        $('body')[0].scrollIntoView(true);
      }
    };

    // On node selection: load the informationobject's page and insert the current page
    var selectNodeListener = function (e, data)
    {
      // Remove any alerts
      $('.app-alert').remove();

      // Open node if possible
      data.instance.open_node(data.node);

      // When an element is clicked in the tree ... fetch it up
      // window.location = window.location.origin + data.node.a_attr.href
      var url = data.node.a_attr.href;
      $.get(url, function (response)
      {
        response = $(response);

        // Insert new content into page
        $('#main-column h1').replaceWith($(response.find('#main-column h1')));
        $('#main-column .breadcrumb').replaceWith($(response.find('#main-column .breadcrumb')));
        $('#main-column .row').replaceWith($(response.find('#main-column .row')));

        $('#main-column > div.messages.error').remove();
        $('#main-column .breadcrumb').after($(response.find('#main-column > div.messages.error')));


        // Attach the Drupal Behaviour so blank.js does its thing
        Drupal.attachBehaviors(document)

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

    // Initialize jstree with options and listeners
    $fwTreeView
      .jstree(options)
      .bind('ready.jstree', readyListener)
      .bind('select_node.jstree', selectNodeListener)
      .bind('hover_node.jstree', hoverNodeListener)
      .bind('open_node.jstree', openNodeListener);

    // TODO restore window.history states
    $(window).bind('popstate', function() {});
  }
})(jQuery);
