'use strict';

var myrickshaw = require('rickshaw');

module.exports = function () {
  return {
    restrict: 'E',
    replace: true,
    scope: {
      'yFilter': '=yFilter'
    },
    template: '<div><rs-y-axis></rs-y-axis><rs-chart></rs-chart><rs-x-axis></rs-x-axis><rs-legend></rs-legend></div>',
    link: function (scope, element, attrs) {

      attrs.$observe('data', function (graphSpecification) {

        var series = [],
            max = Number.MIN_VALUE,
            dataFound = false;

        if (graphSpecification) {
          var xLabels = [];

          // process data for each line of the graph
          JSON.parse(graphSpecification).forEach(function (lineData) {
            // set graph x/y values and increase max y if needed
            for (var i = 0; i < lineData.data.length; i++) {
              // note that data has been found
              dataFound = true;

              // store index as X value because Rickshaw accepts only sequential value
              lineData.data[i].x = i;

              // store real X value as a label to be diplayed using a custom formatter
              if (typeof lineData.xLabelFormat !== 'undefined') {
                if (lineData.xLabelFormat === 'yearAndMonth')
                {
                  xLabels[i] = lineData.data[i].year + '-' + lineData.data[i].month;
                } else {
                  console.log('Invalid xLabelFormat.');
                }
              } else {
                xLabels[i] = parseInt(lineData.data[i][lineData.xProperty]);
              }

              // store y value data
              lineData.data[i].y = lineData.data[i][lineData.yProperty];

              // determine whether a new Y data ceiling should be set
              max = Math.max(max, lineData.data[i].y);
            }
            series.push(lineData);
          });

          // add padding to max
          max = max + (max / 10);

          // set optional element ID
          if (typeof attrs.id !== 'undefined') {
            element.attr('id', attrs.id);
          }

          if (dataFound) {
            var graph = new myrickshaw.Graph({
              element: element.find('rs-chart')[0],
              width: attrs.width,
              height: attrs.height,
              series: series,
              max: max
            });

            // use custom X axis formatter so we can use arbitrary X values
            var xFormat = function (i) {
              return (typeof xLabels[i] !== 'undefined') ? xLabels[i] : '';
            };

            var xAxis = new myrickshaw.Graph.Axis.X({
              graph: graph,
              element: element.find('rs-x-axis')[0],
              orientation: 'bottom',
              pixelsPerTick: attrs.xperTick,
              tickFormat: xFormat
            });
            xAxis.render();

            // allow optional use of Y axis formatter passed in as attribute
            var yFormat = (typeof scope.yFilter !== 'undefined') ? scope.yFilter : myrickshaw.Fixtures.Number.formatKMBT;

            var yAxis = new myrickshaw.Graph.Axis.Y({
              graph: graph,
              element: element.find('rs-y-axis')[0],
              pixelsPerTick: attrs.yperTick,
              orientation: 'left',
              tickFormat: yFormat
            });
            yAxis.render();

            var lineLegend = new myrickshaw.Graph.Legend({
              graph: graph,
              element: element.find('rs-legend')[0]
            });
            lineLegend.render();

            graph.setRenderer(attrs.type);
            graph.render();
          }
        }
      });
    }
  };
};
