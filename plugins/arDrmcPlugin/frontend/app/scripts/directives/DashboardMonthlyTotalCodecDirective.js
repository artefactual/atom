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
      var margin = {top: 30, right: 40, bottom: 30, left: 50},
      width = 600 - margin.left - margin.right,
      height = 270 - margin.top - margin.bottom;

      var parseDate = myd3.time.format('%d-%b-%y').parse;
      var x = myd3.time.scale().range([0, width]);
      var y = myd3.scale.linear().range([height, 0]);

      var xAxis = myd3.svg.axis().scale(x)
        .orient('bottom').ticks(5);

      var yAxis = myd3.svg.axis().scale(y)
        .orient('left').ticks(5);
      var color = myd3.scale.category10();

      var data = [
        {
          'date': parseDate('13-Apr-14'),
          'close': 52,
          'open': 123
        },
        {
          'date': parseDate('13-May-14'),
          'close': 52,
          'open': 1223
        },
        {
          'date': parseDate('13-Jun-14'),
          'close': 552,
          'open': 123
        },
        {
          'date': parseDate('13-Jul-14'),
          'close': 252,
          'open': 123
        },
        {
          'date': parseDate('13-Aug-14'),
          'close': 14,
          'open': 1212
        }
      ];
      // Build data key list
      var data_keys = [];
      angular.forEach (data[0], function (value, key) {
        if(key !== 'date') {
          data_keys.push(key);
        }
      });

      var svg = myd3.select(element[0])
        .append('svg')
        .data([data])
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g')
        .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      // Scale the range of the data
      x.domain(myd3.extent(data, function (d) { return d.date; }));
      y.domain([0, myd3.max(data, function (d) { return Math.max(d.close, d.open); })]);

      // Iterate data key list to draw lines
      angular.forEach (data_keys, function (value, key) {

        var thisvalueline = myd3.svg.line()
          .x(function (d) { return x(d.date);})
          .y(function (d) { return y(d[value]);});
        svg.append('path')    // Add the valueline path.
          .attr('class', 'line')
          .attr('d', thisvalueline(data))
          .style({'stroke': color(key), 'stroke-width': 3, 'fill': 'none'});
        svg.append('text')
          .attr('transform', 'translate(' + (width + 3) + ',' + y(data[0][value]) + ')')
          .attr('dy', '.35em')
          .attr('text-anchor', 'start')
          .style('fill', color(key))
          .text('Open');
      });

      svg.append('g')     // Add the X Axis
        .attr('class', 'x axis')
        .attr('transform', 'translate(0,' + height + ')')
        .call(xAxis);

      svg.append('g')     // Add the Y Axis
        .attr('class', 'y axis')
        .call(yAxis);

    }
  };
};
