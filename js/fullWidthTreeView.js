(function ($) {

  "use strict";

  $(init);

  var treeview_open = false;
  var url = '/informationobject/fullWidthTreeView'
  var html =  "<div id=\"fullwidth-treeview-header\">" +
               "<div class=\"title\">Title</div>" +
               "<div class=\"identifier\">Identifier</div>" +
               "<div class=\"type\">Type</div>" +
              "</div>" +
              "<div id=\"fullwidth-treeview-row\">" + 
               "<div id=\"fullwidth-treeview\">" +
                "<img src=\"/images/loading.gif\" id=\"fullwidth-treeview-loading\" />" +
               "</div>" +
              "</div>";

  // toggles between disabling Holdings tab
  function toggleTreeviewMenu() {
    // get the treeview/holdings tab
    var holdings_tab = $("#treeview-menu li a[data-toggle='#treeview']").parent();

    // activate the search tab
    $("#treeview-menu li a[data-toggle='#treeview-search']").click();

    // disable the holdings tab
    holdings_tab.find('a').addClass('disabled');
    holdings_tab.addClass('disabled');
   
  }

  function init()
  {

    $('#treeview-btn-area i').tooltip({placement: "top"})

    $('.fullwidth-treeview-toggle').click(function(){
      treeview_open = !treeview_open

      // closing fullwidth view?
      if( treeview_open == false )
      {
        // refresh the page
        window.location.reload();
        return false;
      }

      $(this).toggleClass('active');
      // track and toggle state

      toggleTreeviewMenu();

      $('#main-column h1').after($(html));
      $('#fullwidth-treeview-row').animate({height: '100px'}, 500);

      // initialize the jstree with json from server
      $.get((window.location.pathname + url), function(data){
        // configure jstree grid columns
        data.plugins = ['grid'];
        data.grid = {
          columns: [
            {width: 643, value: 'title', title: '_DATA_'},
            {width: 100, value: 'identifier', title: 'identifier'},
            {width: 100, value: 'type', title: 'type'}
          ],
          width: 150
        }

        debug = data;

        // initialize jstree
        $('#fullwidth-treeview').jstree(data);
        $('#fullwidth-treeview-row').resizable({ 
          handles: "s"
        }).animate({height: '200px'}, 500);
      });

      // bind click events to nodes to load the informationobject's page and insert the current page
      $("#fullwidth-treeview").bind("select_node.jstree", function(evt, data){
        // open node if possible
        data.instance.open_node(data.node);

        // when an element is clicked in the tree ... fetch it up
        // window.location = window.location.origin + data.node.a_attr.href
        var url = window.location.origin + '/index.php/' + data.node.a_attr.href;
        $.get(url, function(response){
          response = $(response);

          // insert new content into page
          $('#main-column h1').replaceWith($(response.find('#main-column h1')));
          $('#main-column .breadcrumb').replaceWith($(response.find('#main-column .breadcrumb')));
          $('#main-column .row').replaceWith($(response.find('#main-column .row')));

          // attach the Drupal Behaviour so blank.js does its thing.
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