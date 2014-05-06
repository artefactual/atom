'use strict';

var myd3 = require('d3');
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

      attrs.$observe('chartData', function (chartAttr) {
        var datum = chartAttr;

        if (attrs.type === 'pie') {

          // ---------------------------
          // from storage codec directive
          // ---------------------------

          var w = attrs.width,
            h = attrs.height,
            r = w / 2 * 0.8,
            //inner =  w * 0.1,
            color = myd3.scale.category20c();

          var svg = myd3.select(element[0])
            .append('svg:svg')
            .data(datum)
            .style ('stroke', 'white')
            .attr('width', w)
            .attr('height', h)
            .append('svg:g')
            .attr('transform', 'translate(' + r * 1.1 + ',' + r * 1.1 + ')');

          var arc = myd3.svg.arc()
            //.innerRadius(inner)
            .outerRadius(r);

          var pie = myd3.layout.pie()
            .value(function (d) { return d; });

          var arcs = svg.selectAll('g.slice')
            .data(pie)
            .enter()
            .append('svg:g')
            .attr('class', 'slice');

          arcs.append('svg:path')
              .attr('fill', function (d, i) { return color(i);})
              .attr('d', arc);

          var label_width = Number.MIN_VALUE;

          for (var i = 0; i < datum.length; i++) {
            label_width = Math.max(label_width, datum[i].label.length);
            console.log(datum[i]);
          }
          // round up to 10th
          label_width = label_width * 8;
          //var label_height = datum.length * 18;

          var legend = myd3.select(element[0]).append('svg')
            .attr('class', 'legend')
            .attr('width', w)
            .attr('height', h)
            .selectAll('g')
            .data(color.domain().slice().reverse())
            .enter().append('g')
            .attr('transform', function (d, i) { return 'translate(0,' + i * 18 + ')';});

          legend.append('rect')
            .attr('width', 18)
            .attr('height', 18)
            .style('fill', color);

          legend.append('text')
            .attr('x', 24)
            .attr('y', 9)
            .attr('dy', '.35em')
            .text(function (d) {
              var label = datum[d].label;
              return label;
            });
        } else if (attrs.type === 'line') {

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

        } else {
          return;
        }
      });
    }
  };
};
