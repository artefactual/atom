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
          var xLabels = [];

          // process data for each line of the graph
          JSON.parse(graphSpecification).forEach(function (lineData) {
            // set graph x/y values and increase max y if needed
            for (var i = 0; i < lineData.data.length; i++) {
              // store index as X value because Rickshaw accepts only sequential value
              lineData.data[i].x = i;

              // store real X value as a label to be diplayed using a custom formatter
              xLabels[i] = parseInt(lineData.data[i][lineData.xProperty]);

              // store y value data
              lineData.data[i].y = lineData.data[i][lineData.yProperty];

              // determine whether a new Y data ceiling should be set
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

          // use custom X axis formatter so we can use arbitrary X values
          var format = function (i) {
            return (typeof xLabels[i] !== 'undefined') ? xLabels[i] : '';
          };

          var xAxis = new myrickshaw.Graph.Axis.X({
            graph: graph,
            element: element.find('rs-x-axis')[0],
            orientation: 'bottom',
            pixelsPerTick: attrs.xperTick,
            tickFormat: format
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
