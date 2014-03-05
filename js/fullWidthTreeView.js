(function ($) {

  "use strict";

  $(init);

  var treeview_open = false;
  var url = '/informationobject/fullwidthtreeview'

  function init()
  {
    $('#fullwidth-treeview-toggle').click(function(){
    $('.fullwidth-treeview-toggle').click(function(){
      // track and toggle state

      treeview_open = !treeview_open
      if( treeview_open == false )
      {
        $('#fullwidth-treeview-row').remove();
        return;
      }

      // check we've not already inserted the DOM elements for this
      if( !$('#fullwidth-treeview-row').length )
      {
        $('#wrapper').prepend($("<div class=\"row\" id=\"fullwidth-treeview-row\"><div id=\"fullwidth-treeview\"></div></div>"));
      }

      // initialize the jstree with json from server
      $.get((window.location.pathname + url), function(data){
        $('#fullwidth-treeview').jstree(data);
        $('#fullwidth-treeview-row').resizable({ 
          handles: "s"
        }).height('350px');

      });

      $("#fullwidth-treeview").bind("select_node.jstree", function(evt, data){
        // when an element is clicked in the tree ... fetch it up
        window.location = window.location.origin + data.node.a_attr.href
      });
    });
  }
})(jQuery);