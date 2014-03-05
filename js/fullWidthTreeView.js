(function ($) {

  "use strict";

  $(init);

  var treeview_open = false;
  var url = '/informationobject/fullwidthtreeview'
  var html = "<div class=\"row\" id=\"fullwidth-treeview-row\">" + 
               "<div id=\"fullwidth-treeview\">" +
                "<img src=\"/images/loading.gif\" id=\"fullwidth-treeview-loading\" />" +
               "</div>" +
              "</div>";

  function init()
  {
    $('.fullwidth-treeview-toggle').click(function(){
      $(this).toggleClass('active');
      // track and toggle state

      treeview_open = !treeview_open
      if( treeview_open == false )
      {
        $('#fullwidth-treeview-row').animate({opacity: 0, height: "0px"}, 1000, "linear", function() { $(this).remove(); });
        return;
      }

      // check we've not already inserted the DOM elements for this
      if( !$('#fullwidth-treeview-row').length )
      {
        $('#wrapper').prepend($(html));
        $('#fullwidth-treeview-row').animate({height: '100px'}, 500);
      }

      // initialize the jstree with json from server
      $.get((window.location.pathname + url), function(data){
        $('#fullwidth-treeview').jstree(data);
        $('#fullwidth-treeview-row').resizable({ 
          handles: "s"
        }).animate({height: '350px'}, 500);

      });

      $("#fullwidth-treeview").bind("select_node.jstree", function(evt, data){
        // when an element is clicked in the tree ... fetch it up
        window.location = window.location.origin + data.node.a_attr.href
      });
    });
  }
})(jQuery);