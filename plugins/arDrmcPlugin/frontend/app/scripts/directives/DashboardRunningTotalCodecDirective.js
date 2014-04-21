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
          data: [ { x: 41365, y: 5 }, { x: 41395, y: 10 }, { x: 41426, y: 16 }, { x: 41456, y: 23 }, { x: 41487, y: 31 }, { x: 41518, y: 36 }, { x: 41548, y: 41 }, { x: 41579, y: 46 }, { x: 41609, y: 52 }, { x: 41640, y: 58 }, { x: 41671, y: 66 }, { x: 41699, y: 71 } ],
          color: palette.color()
        },
        {
          name: 'apple prores 422',
          data: [ { x: 41365, y: 3 }, { x: 41395, y: 5 }, { x: 41426, y: 7 }, { x: 41456, y: 9 }, { x: 41487, y: 12 }, { x: 41518, y: 15 }, { x: 41548, y: 19 }, { x: 41579, y: 23 }, { x: 41609, y: 28 }, { x: 41640, y: 31 }, { x: 41671, y: 34 }, { x: 41699, y: 37 } ],
          color: palette.color()
        },
        {
          name: 'H264',
          data: [ { x: 41365, y: 5 }, { x: 41395, y: 10 }, { x: 41426, y: 15 }, { x: 41456, y: 20 }, { x: 41487, y: 26 }, { x: 41518, y: 32 }, { x: 41548, y: 41 }, { x: 41579, y: 47 }, { x: 41609, y: 53 }, { x: 41640, y: 59 }, { x: 41671, y: 66 }, { x: 41699, y: 73 } ],
          color: palette.color()
        },
        {
          name: 'Animation',
          data: [ { x: 41365, y: 6 }, { x: 41395, y: 10 }, { x: 41426, y: 13 }, { x: 41456, y: 16 }, { x: 41487, y: 19 }, { x: 41518, y: 21 }, { x: 41548, y: 23 }, { x: 41579, y: 25 }, { x: 41609, y: 27 }, { x: 41640, y: 28 }, { x: 41671, y: 29 }, { x: 41699, y: 30 } ],
          color: palette.color()
        },
        {
          name: 'MPEG-2',
          data: [ { x: 41365, y: 7 }, { x: 41395, y: 13 }, { x: 41426, y: 19 }, { x: 41456, y: 24 }, { x: 41487, y: 29 }, { x: 41518, y: 33 }, { x: 41548, y: 36 }, { x: 41579, y: 38 }, { x: 41609, y: 40 }, { x: 41640, y: 41 }, { x: 41671, y: 42 }, { x: 41699, y: 43 } ],
          color: palette.color()
        },
        {
          name: 'FFV!',
          data: [ { x: 41365, y: 8 }, { x: 41395, y: 8 }, { x: 41426, y: 8 }, { x: 41456, y: 8 }, { x: 41487, y: 12 }, { x: 41518, y: 17 }, { x: 41548, y: 23 }, { x: 41579, y: 30 }, { x: 41609, y: 37 }, { x: 41640, y: 46 }, { x: 41671, y: 56 }, { x: 41699, y: 66 } ],
          color: palette.color()
        },
        {
          name: 'DV',
          data: [ { x: 41365, y: 9 }, { x: 41395, y: 17 }, { x: 41426, y: 24 }, { x: 41456, y: 30 }, { x: 41487, y: 35 }, { x: 41518, y: 39 }, { x: 41548, y: 43 }, { x: 41579, y: 46 }, { x: 41609, y: 49 }, { x: 41640, y: 51 }, { x: 41671, y: 53 }, { x: 41699, y: 55 } ],
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
        width: 440,
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
