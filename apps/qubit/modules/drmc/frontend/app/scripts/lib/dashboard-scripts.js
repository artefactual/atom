'use strict';

jQuery(document).ready(function(){

  jQuery.getJSON(Qubit.relativeUrlRoot + "/apps/qubit/modules/drmc/frontend/app/scripts/lib/dashboard-dummy-data.json",function(obj){

     jQuery.each(obj.dash,function(key,value) {

        //dropdown lists
        jQuery("#select-AIP-new").append('<li><a href=\"#\">AIP# ' + value.id + ' - ' + value.registered + '</a></li>');

        jQuery("#select-AIP-awaiting").append('<li><a href=\"#\">AIP# ' + value.id + ' - ' + value.name + value.gender + ' - ' + value.balance + '</a></li>');
         });

  });

    d3.select("#AIP-mime-type").append('p').text('new paragraph');

});
