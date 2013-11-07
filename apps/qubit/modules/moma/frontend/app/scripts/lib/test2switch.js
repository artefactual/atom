'use strict';

function switchData(){

      var obj = jQuery('#node-10');
      var a1  = jQuery('#aside1');
      var a2  = jQuery('#aside2');
      console.log(obj, a1, a1, 'Hi from heather');



     function onClick(){
      a1.css("display", "none");
    };

};

    obj.onclick = switchData();


