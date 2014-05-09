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

      attrs.$observe('data', function (graphSpecification) {
        var series = [],
            max = Number.MIN_VALUE;

        if (graphSpecification) {
          // process data for each line of the graph
          JSON.parse(graphSpecification).forEach(function (lineData) {
            // set graph x/y values and increase max y if needed
            for (var i = 0; i < lineData.data.length; i++) {
              lineData.data[i].x = parseInt(lineData.data[i][lineData.xProperty]);
              lineData.data[i].y = lineData.data[i][lineData.yProperty];
              max = Math.max(max, lineData.data[i].y);
            }
            series.push(lineData);
          });

          // round max up to 10th
          max = Math.ceil(max / 10) * 10;

          var graph = new myrickshaw.Graph({
            element: element.find('rs-chart')[0],
            width: attrs.width,
            height: attrs.height,
            series: series,
            max: max
          });

          var xAxis = new myrickshaw.Graph.Axis.X({
            graph: graph,
            element: element.find('rs-x-axis')[0],
            orientation: 'bottom',
            pixelsPerTick: attrs.xperTick,
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
