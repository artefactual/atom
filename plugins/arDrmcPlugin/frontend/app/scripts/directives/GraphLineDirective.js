'use strict';

var myrickshaw = require('rickshaw');

module.exports = function () {
  return {
    restrict: 'E',
    replace: true,
    scope: {
    },
    template: '<div><rs-y-axis></rs-y-axis><rs-chart></rs-chart><rs-x-axis></rs-x-axis><rs-legend></rs-legend></div>',
    link: function (scope, element, attrs) {

      attrs.$observe('data', function (newAttrs) {
        var max = Number.MIN_VALUE;

        console.log('observing');
        if (newAttrs) {
          var datum = JSON.parse(newAttrs);

          // ---------------------------
          // from line chart directive
          // ---------------------------

          // set graph x/y values
          for (var i = 0; i < datum.length; i++
            ) {
            datum[i].x = parseInt(datum[i].year);
            datum[i].y = datum[i].average;
            max = Math.max(max, datum[i].y);
          }

          console.log('aaaa');
          console.log(datum);
          /*
          var palette = new myrickshaw.Color.Palette();
          for (var y = 0; y < datum.length; y++
            ) {
            //datum[y].color = palette.color();
            //for (var j = 0; j < datum[y].data.length; j++) {
            //  max = Math.max(max, datum[y].data[j].y);
            //}
            //
          }
          */

          // round up to 10th
          max = Math.ceil(max / 10) * 10;

          var graph = new myrickshaw.Graph({
            element: element.find('rs-chart')[0],
            width: attrs.width,
            height: attrs.height,
            series: [{color: 'steelblue', data: datum}],
            max: max
          });

          var xAxis = new myrickshaw.Graph.Axis.X({
            graph: graph,
            element: element.find('rs-x-axis')[0],
            orientation: 'bottom',
            pixelsPerTick: attrs.xperTick
          });
          xAxis.render();

          var yAxis = new myrickshaw.Graph.Axis.Y({
            graph: graph,
            element: element.find('rs-y-axis')[0],
            pixelsPerTick: attrs.yperTick,
            orientation: 'left',
            tickFormat: myrickshaw.Fixtures.Number.formatKMBT
          });
          yAxis.render();

          var lineLegend = new myrickshaw.Graph.Legend({
            graph: graph,
            element: element.find('rs-legend')[0]
          });
          lineLegend.render();

          graph.setRenderer('line');
          graph.render();
        }
      });
    }
  };
};
