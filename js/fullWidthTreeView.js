(function ($) {

  "use strict";

  $(init);

  var treeview_open = false;
  var url = '/informationobject/fullWidthTreeView'
  var html = "<div id=\"fullwidth-treeview-row\">" + 
               "<div id=\"fullwidth-treeview\">" +
                "<img src=\"/images/loading.gif\" id=\"fullwidth-treeview-loading\" />" +
               "</div>" +
              "</div>";

  // toggles between disabling Holdings tab
  function toggleTreeviewMenu() {
    // get the treeview/holdings tab
    var tab = $("#treeview-menu li a[data-toggle='#treeview']").parent();

    // we closing/removing treeview?
    if(arguments[0] == 'close')
    {
      tab.find('a').removeClass('disabled');
      tab.removeClass('disabled');
    }

    if(arguments[0] == 'open') {
      // set sidebar treeview to Quick search tab & disable Holdings tab
      $("#treeview-menu li a[data-toggle='#treeview-search']").click();
      
      // disable the bootstrap tab
      tab.find('a').addClass('disabled');

      // also have to disable the surrounding tab to prevent the "click" cursor
      tab.addClass('disabled');
    }
  }

  function init()
  {

    $('#treeview-btn-area i').tooltip({placement: "top"})

    $('.fullwidth-treeview-toggle').click(function(){
      $(this).toggleClass('active');
      // track and toggle state

      treeview_open = !treeview_open
      if( treeview_open == false )
      {
        $('#fullwidth-treeview-row').animate({opacity: 0, height: "0px"}, 1000, "linear", function() { $(this).remove(); });
        toggleTreeviewMenu('close');
        return;
      }
      toggleTreeviewMenu('open');

      // check we've not already inserted the DOM elements for this
      if( !$('#fullwidth-treeview-row').length )
      {
        $('#main-column h1').after($(html));
        $('#fullwidth-treeview-row').animate({height: '100px'}, 500);
      }

      // initialize the jstree with json from server
      $.get((window.location.pathname + url), function(data){
        $('#fullwidth-treeview').jstree(data);
        $('#fullwidth-treeview-row').resizable({ 
          handles: "s"
        }).animate({height: '350px'}, 500);
      });

      // bind click events to nodes to load the informationobject's page and insert the current page
      $("#fullwidth-treeview").bind("select_node.jstree", function(evt, data){
        // when an element is clicked in the tree ... fetch it up
        // window.location = window.location.origin + data.node.a_attr.href
        var url = window.location.origin + '/index.php/' + data.node.a_attr.href;
        $.get(url, function(response){
          // copy the treeview to the new object information response
          // insert the new object information into the existing page
          response = $(response);
          $('#fullwidth-treeview-row').insertAfter(response.find('#main-column h1'));
          $('#main-column').replaceWith($(response.find('#main-column')));

          // attach the Deupal Behaviour so blank.js does its thing.
          Drupal.attachBehaviors(document)

          // update the url, TODO save the state
          window.history.pushState({}, $('#main-column h1').first().text(), url);
        });
      });

      // TODO restore window.history states
      $(window).bind('popstate', function() {

      });
    });
  }
})(jQuery);

var debug = {};