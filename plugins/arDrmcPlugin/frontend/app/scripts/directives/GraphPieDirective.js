'use strict';

var myd3 = require('d3');

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
          // from storage codec directive
          // ---------------------------

          var w = attrs.width,
            h = attrs.height,
            r = w / 2 * 0.8,
            //inner =  w * 0.1,
            color = myd3.scale.category20c();

          var svg = myd3.select(element[0])
            .append('svg:svg')
            .data([datum])
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
            }
          );
        }
      });

    }
  };
};
