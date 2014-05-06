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
      // var datum = angular.fromJson(attrs.chartData);

      attrs.$observe('data', function (newAttrs) {

        if (newAttrs) {
          var datum = newAttrs;

          // ---------------------------
          // from line chart directive
          // ---------------------------

          var palette = new myrickshaw.Color.Palette();
          var max = Number.MIN_VALUE;
          for (var y = 0; y < datum.length; y++
            ) {
            datum[y].color = palette.color();
            for (var j = 0; j < datum[y].data.length; j++) {
              max = Math.max(max, datum[y].data[j].y);
            }
          }
          // round up to 10th
          max = Math.ceil(max / 10) * 10;

          var graph = new myrickshaw.Graph({
            element: element.find('rs-chart')[0],
            width: attrs.width,
            height: attrs.height,
            series: datum,
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
