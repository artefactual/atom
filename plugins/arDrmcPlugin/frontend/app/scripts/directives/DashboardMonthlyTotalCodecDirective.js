'use strict';

var myrickshaw = require('rickshaw');

module.exports = function () {
  return {
    restrict: 'E',
    scope: {
    },
    link: function (scope, element) {
      var palette = new myrickshaw.Color.Palette();
      var dataset = [
        {
          name: 'Uncompressed 10-bit',
          data: [ { x: 41365, y: 5 }, { x: 41395, y: 5 }, { x: 41426, y: 6 }, { x: 41456, y: 7 }, { x: 41487, y: 8 }, { x: 41518, y: 5 }, { x: 41548, y: 5 }, { x: 41579, y: 5 }, { x: 41609, y: 4 }, { x: 41640, y: 6 }, { x: 41671, y: 8 }, { x: 41699, y: 5 } ],
          color: palette.color()
        },
        {
          name: 'apple prores 422',
          data: [ { x: 41365, y: 3 }, { x: 41395, y: 2 }, { x: 41426, y: 2 }, { x: 41456, y: 2 }, { x: 41487, y: 3 }, { x: 41518, y: 3 }, { x: 41548, y: 4 }, { x: 41579, y: 4 }, { x: 41609, y: 5 }, { x: 41640, y: 3 }, { x: 41671, y: 3 }, { x: 41699, y: 3 } ],
          color: palette.color()
        },
        {
          name: 'H264',
          data: [ { x: 41365, y: 5 }, { x: 41395, y: 5 }, { x: 41426, y: 5 }, { x: 41456, y: 5 }, { x: 41487, y: 6 }, { x: 41518, y: 6 }, { x: 41548, y: 8 }, { x: 41579, y: 9 }, { x: 41609, y: 6 }, { x: 41640, y: 5 }, { x: 41671, y: 6 }, { x: 41699, y: 7 } ],
          color: palette.color()
        },
        {
          name: 'Animation',
          data: [ { x: 41365, y: 4 }, { x: 41395, y: 4 }, { x: 41426, y: 3 }, { x: 41456, y: 3 }, { x: 41487, y: 3 }, { x: 41518, y: 2 }, { x: 41548, y: 3 }, { x: 41579, y: 2 }, { x: 41609, y: 2 }, { x: 41640, y: 2 }, { x: 41671, y: 1 }, { x: 41699, y: 1 } ],
          color: palette.color()
        },
        {
          name: 'MPEG-2',
          data: [ { x: 41365, y: 6 }, { x: 41395, y: 6 }, { x: 41426, y: 6 }, { x: 41456, y: 5 }, { x: 41487, y: 5 }, { x: 41518, y: 4 }, { x: 41548, y: 4 }, { x: 41579, y: 3 }, { x: 41609, y: 2 }, { x: 41640, y: 1 }, { x: 41671, y: 1 }, { x: 41699, y: 1 } ],
          color: palette.color()
        },
        {
          name: 'FFV!',
          data: [ { x: 41365, y: 0 }, { x: 41395, y: 0 }, { x: 41426, y: 0 }, { x: 41456, y: 0 }, { x: 41487, y: 4 }, { x: 41518, y: 5 }, { x: 41548, y: 5 }, { x: 41579, y: 6 }, { x: 41609, y: 7 }, { x: 41640, y: 8 }, { x: 41671, y: 9 }, { x: 41699, y: 10 } ],
          color: palette.color()
        },
        {
          name: 'DV',
          data: [ { x: 41365, y: 8 }, { x: 41395, y: 8 }, { x: 41426, y: 7 }, { x: 41456, y: 6 }, { x: 41487, y: 5 }, { x: 41518, y: 4 }, { x: 41548, y: 4 }, { x: 41579, y: 4 }, { x: 41609, y: 3 }, { x: 41640, y: 2 }, { x: 41671, y: 2 }, { x: 41699, y: 2 } ],
          color: palette.color()
        }
      ];

      var max = Number.MIN_VALUE;
      for (var i = 0; i < dataset.length; i++) {
        for (var j = 0; j < dataset[i].data.length; j++) {
          max = Math.max(max, dataset[i].data[j].y);
        }
      }
      // round up to 10th
      max = Math.ceil(max / 10) * 10;

      var graph = new myrickshaw.Graph({
        element: element.find('rs-chart')[0],
        width: 640,
        height: 240,
        series: dataset,
        max: max
      });
      var xAxis = new myrickshaw.Graph.Axis.X({
        graph: graph,
        element: element.find('rs-x-axis')[0],
        orientation: 'bottom'
      });
      xAxis.render();

      var yAxis = new myrickshaw.Graph.Axis.Y({
        graph: graph,
        element: element.find('rs-y-axis')[0],
        pixelsPerTick: 20,
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
