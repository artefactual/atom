(function ($) {

  "use strict";

  $(init);

  function init()
  {
    $('#fullwidth-treeview-toggle').click(function(){
      var url = "http://10.10.10.10:8002/index.php/islamic-fundamentalist-audio-recordings-collection/informationobject/fullwidthtreeview";

      // check we've not already inserted the DOM elements for this
      if( !$('#fullwidth-treeview').length )
      {
        $('#wrapper').prepend($("<div class=\"row\"><div id=\"fullwidth-treeview\"></div></div>"));
      }

      // initialize the jstree with json from server
      $.get(url, function(data){
        $('#fullwidth-treeview').jstree(data);
        $('#fullwidth-treeview').resizable({ 
          minHeight: 250,
          maxHeight: 350,
          handles: "s"
        });
      });
      
    });  
  }


})(jQuery);
