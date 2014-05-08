'use strict';

var arD3 = require('d3');

module.exports = function () {
  return {
    restrict: 'E',
    scope: {
    },
    template: '<rs-y-axis></rs-y-axis><rs-chart></rs-chart><rs-x-axis></rs-x-axis><rs-legend></rs-legend>',
    link: function (scope, element, attrs) {
      // var datum = angular.fromJson(attrs.chartData);
      attrs.$observe('data', function (newAttrs) {

        if (newAttrs && attrs.width) {
          // Width and height
          // Pies are round (w = h)
          var w = attrs.width;
          var h = attrs.width;
          var color = arD3.scale.category20();

          newAttrs = JSON.parse(newAttrs);

          var dataset = newAttrs;

          var outerRadius = w / 2;
          var innerRadius = 0;
          var arc = arD3.svg.arc()
            .innerRadius(innerRadius)
            .outerRadius(outerRadius);

          var pie = arD3.layout.pie()
            .value(function (d) {
              return d.count;
            });

          //Create SVG element
          var svg = arD3.select(element[0])
            .append('svg')
            .attr('width', w)
            .attr('height', h);

          //Set up groups
          var arcs = svg.selectAll('g.arc')
            .data(pie(dataset))
            .enter()
            .append('g')
            .attr('class', 'arc')
            .attr('transform', 'translate(' + outerRadius + ',' + outerRadius + ')');

          //Draw arc paths
          arcs.append('path')
            .attr('fill', function (d, i) {
              return color(i);
            })
            .attr('d', arc);

          //Labels
          /*arcs.append('svg:text')
            .attr('transform', function (d) {
              return 'translate(' + arc.centroid(d) + ')';
            })
            .attr('text-anchor', 'middle')
            .text(function (d) {
              return d.data.media_type;
            });*/

          // Legend
          var label_width = Number.MIN_VALUE;

          for (var i = 0; i < dataset.length; i++) {
            label_width = Math.max(label_width, dataset[i].media_type.length);
          }
          // round up to 10th
          label_width = label_width * 9;
          var label_height = dataset.length * 20;

          var legend = arD3.select(element[0]).append('svg')
            .attr('class', 'legend')
            .attr('width', label_width)
            .attr('height', label_height)
            .selectAll('g')
            .data(color.domain().slice().reverse())
            .enter().append('g')
            .attr('transform', function (d, i) { return 'translate(0,' + i * 20 + ')'; });

          legend.append('rect')
            .attr('width', 18)
            .attr('height', 18)
            .style('fill', color);

          legend.append('text')
            .attr('x', 24)
            .attr('y', 14)
            //.attr('dy', '.35em')
            .text(function (d) {
              var value = dataset[d].count;
              return value;
            });

          legend.append('text')
          .attr('x', 54)
          .attr('y', 14)
          .text(function (d) {
            var label = dataset[d].media_type;
            return label;
          });
        }
      });
    }
  };
};
