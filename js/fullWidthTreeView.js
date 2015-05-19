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

  $(init);

  var treeview_open = false;
  var url = '/informationobject/fullWidthTreeView';
  var html =  "<div id=\"fullwidth-treeview-row\">" + 
               "<div id=\"fullwidth-treeview\">" +
               "</div>" +
              "</div>";
  var loader = null;

  // Toggles between disabling Holdings tab
  function toggleTreeviewMenu()
  {
    // Get the treeview/holdings tab
    var holdings_tab = $("#treeview-menu li a[data-toggle='#treeview']").parent();

    // Activate the search tab
    $("#treeview-menu li a[data-toggle='#treeview-search']").click();

    // Disable the holdings tab
    holdings_tab.find('a').addClass('disabled');
    holdings_tab.addClass('disabled');
  }

  function init()
  {
    $('#treeview-btn-area i').tooltip({placement: "top"});

    loadTreeView();
  }

  function loadTreeView()
  {
    treeview_open = !treeview_open

    // Closing fullwidth view?
    if (treeview_open == false)
    {
      // Refresh the page
      loader.toggle();
      window.location.reload();

      return false;
    }

    $(this).toggleClass('active');

    // Track and toggle state
    toggleTreeviewMenu();

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
        },

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

      // Hide loader
      loader = $('#fullwidth-treeview-loading');
      loader.toggle();
    });

    // Bind click events to nodes to load the informationobject's page and insert the current page
    $("#fullwidth-treeview").bind("select_node.jstree", function(evt, data)
    {
      // Set icon to spinner
      loader.toggle();

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
        History.pushState(null, $('#main-column h1').first().text(), url);

        // Remove loading icon
        loader.toggle();
      });
    });

    // TODO restore window.history states
    $(window).bind('popstate', function() {});
  }
})(jQuery);
