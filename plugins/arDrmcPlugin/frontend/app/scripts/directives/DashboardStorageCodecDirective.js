'use strict';

var myd3 = require('d3');

module.exports = function () {
  return {
    restrict: 'E',
    replace: true,
    scope: {
      codec: '@'
    },
    link: function (scope, element) {
      // the D3 bits...
      var data = [
        {
          'label': 'Uncompressed 10-bit',
          'value': 194
        },
        {
          'label': 'apple prores 422',
          'value': 567
        },
        {
          'label': 'H264',
          'value': 114
        },
        {
          'label': 'Animation',
          'value': 793
        },
        {
          'label': 'MPEG-2',
          'value': 929
        },
        {
          'label': 'FFV!',
          'value': 183
        },
        {
          'label': 'DV',
          'value': 1883
        }
      ];
      var codec = data;
      var w = 220,
          h = 220,
          r = w / 2 * 0.8,
          //inner =  w * 0.1,
          color = myd3.scale.category20c();

      var svg = myd3.select(element[0])
        .append('svg:svg')
        .data([codec])
        .style ('stroke', 'white')
        .attr('width', w)
        .attr('height', h)
        .append('svg:g')
        .attr('transform', 'translate(' + r * 1.1 + ',' + r * 1.1 + ')');

      var arc = myd3.svg.arc()
        //.innerRadius(inner)
        .outerRadius(r);

      var pie = myd3.layout.pie()
        .value(function (d) { return d.value; });

      var arcs = svg.selectAll('g.slice')
        .data(pie)
        .enter()
        .append('svg:g')
        .attr('class', 'slice');

      arcs.append('svg:path')
          .attr('fill', function (d, i) { return color(i);})
          .attr('d', arc);

      var legend = myd3.select(element[0]).append('svg')
        .attr('class', 'legend')
        .attr('width', r)
        .attr('height', r * 2)
        .selectAll('g')
        .data(color.domain().slice().reverse())
        .enter().append('g')
        .attr('transform', function (d, i) { return 'translate(0,' + i * 20 + ')';});

      legend.append('rect')
        .attr('width', 18)
        .attr('height', 18)
        .style('fill', color);

      legend.append('text')
        .attr('x', 24)
        .attr('y', 9)
        .attr('dy', '.35em')
        .text(function (d) {
          var label = codec[d].label;
          return label;
        });
    }
  };
};
