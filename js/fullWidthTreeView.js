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

  var url = '/informationobject/fullWidthTreeView';
  var html =  "<div id=\"fullwidth-treeview-row\">" + 
               "<div id=\"fullwidth-treeview\">" +
               "</div>" +
               "</div>";

  function loadTreeView()
  {
    $('#main-column h1').after($(html));
    $('#fullwidth-treeview-row').animate({height: '100px'}, 500);

    // Initialize the jstree with json from server
    $.get((window.location.pathname.match("^[^;]*")[0] + url), function(data)
    {
      // Configure jstree grid columns
      data.plugins = ['types'];
      data.types = {
        'default': {
          'icon': 'fa fa-folder-o'
        },

        // Item, File
        'Item': {
          'icon': 'fa fa-file-text-o'
        },
        'File': {
          'icon': 'fa fa-file-text-o'
        },

        // Series, Subseries, Subfonds
        'Series': {
          'icon': 'fa fa-folder-o'
        },
        'Subseries': {
          'icon': 'fa fa-folder-o'
        },
        'subfonds': {
          'icon': 'fa fa-folder-o'
        },
        'Sous-fonds': {
          'icon': 'fa fa-folder-o'
        },
        
        // Fonds, Collection
        'Fonds': {
          'icon': 'fa fa-archive'
        },
        'Collection': {
          'icon': 'fa fa-archive'
        }
      }

      // Initialize jstree
      $('#fullwidth-treeview').jstree(data);
      $('#fullwidth-treeview-row').resizable({handles: 's'}).animate({height: '200px'}, 500);

      // Scroll to active node
      var active_node = null;
      if ( active_node = $('li [selected_on_load]')[0])
      {
        active_node.scrollIntoView(true);
        $('body')[0].scrollIntoView(true);
      }
    });

    // Bind click events to nodes to load the informationobject's page and insert the current page
    $("#fullwidth-treeview").bind("select_node.jstree", function(evt, data)
    {
      // Remove any alerts
      $('#notice-alerts.alert,#error-alerts.alert').remove();

      // Open node if possible
      data.instance.open_node(data.node);

      // When an element is clicked in the tree ... fetch it up
      // window.location = window.location.origin + data.node.a_attr.href
      var url = data.node.a_attr.href;
      $.get(url, function(response)
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
    });

    // Configure tooltip. A reminder is needed each time a node
    // is hovered to make it appear after node changes. It must
    // use the #fullwidth-treeview container to allow a higher
    // height than the node in multiple lines tooltips
    $("#fullwidth-treeview").bind("hover_node.jstree", function (e, data)
    {
      $('a.jstree-anchor').tooltip({
        delay: 250,
        container: '#fullwidth-treeview'
      });
    });

    // Remove tooltip after a node is selected, the node is
    // reloaded and the first tooltip is never removed
    $("#fullwidth-treeview").bind("open_node.jstree", function (e, data)
    {
      $("#fullwidth-treeview .tooltip").remove();
    });

    // TODO restore window.history states
    $(window).bind('popstate', function() {});
  }
})(jQuery);
