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
          'icon': 'icon-folder-close-alt'
        },

        // Item, File
        'Item': {
          'icon': 'icon-file-text-alt'
        },
        'File': {
          'icon': 'icon-file-text-alt'
        },

        // Series, Subseries, Subfonds
        'Series': {
          'icon': 'icon-folder-close-alt'
        },
        'Subseries': {
          'icon': 'icon-folder-close-alt'
        },
        'subfonds': {
          'icon': 'icon-folder-close-alt'
        },
        'Sous-fonds': {
          'icon': 'icon-folder-close-alt'
        },
        
        // Fonds, Collection
        'Fonds': {
          'icon': 'icon-archive'
        },
        'Collection': {
          'icon': 'icon-archive'
        }
      }

      // Initialize jstree
      $('#fullwidth-treeview').jstree(data);
      $('#fullwidth-treeview-row').resizable({ 
        handles: "s"
      }).animate({height: '200px'}, 500);

      // Hack for options like delay and container not working with selector option
      jQuery.fn['tooltip'].defaults.container = '#fullwidth-treeview';
      jQuery.fn['tooltip'].defaults.delay = 300;
      jQuery.fn['tooltip'].defaults.placement = 'top';
      jQuery('#fullwidth-treeview').tooltip({selector: 'a.jstree-anchor'});

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

    // TODO restore window.history states
    $(window).bind('popstate', function() {});
  }
})(jQuery);
