'use strict';

var myrickshaw = require('rickshaw');

module.exports = function () {
  return {
    restrict: 'E',
    scope: {
    },
    template: '<rs-y-axis></rs-y-axis><rs-chart></rs-chart><rs-x-axis></rs-x-axis><rs-legend></rs-legend>',
    link: function (scope, element, attrs) {

      var dataset = angular.fromJson(attrs.chartData);
      var palette = new myrickshaw.Color.Palette();
      var max = Number.MIN_VALUE;

      for (var i = 0; i < dataset.length; i++) {
        dataset[i].color = palette.color();
        for (var j = 0; j < dataset[i].data.length; j++) {
          max = Math.max(max, dataset[i].data[j].y);
        }
      }
      // round up to 10th
      max = Math.ceil(max / 10) * 10;

      var graph = new myrickshaw.Graph({
        element: element.find('rs-chart')[0],
        width: attrs.width,
        height: attrs.height,
        series: dataset,
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

      var legend = new myrickshaw.Graph.Legend({
        graph: graph,
        element: element.find('rs-legend')[0]
      });
      legend.render();

      graph.setRenderer('line');
      graph.render();
    }
  };
};
