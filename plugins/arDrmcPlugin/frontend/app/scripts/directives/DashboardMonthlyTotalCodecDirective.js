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

      var valueline = myd3.svg.line()
        .x(function (d) { return x(d.date);})
        .y(function (d) { return y(d.close);});

      var valueline2 = myd3.svg.line()
        .x(function (d) { return x(d.date);})
        .y(function (d) { return y(d.open);});

      var data = [
        {
          'date': parseDate('13-Apr-14'),
          'close': 52,
          'open': 123
        },
        {
          'date': parseDate('13-May-14'),
          'close': 52,
          'open': 123
        },
        {
          'date': parseDate('13-Jun-14'),
          'close': 52,
          'open': 123
        },
        {
          'date': parseDate('13-Jul-14'),
          'close': 52,
          'open': 123
        },
        {
          'date': parseDate('13-Aug-14'),
          'close': 14,
          'open': 1212
        }
      ];


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

      svg.append('path')    // Add the valueline path.
        .attr('class', 'line')
        .attr('d', valueline(data));

      svg.append('path')    // Add the valueline2 path.
        .attr('class', 'line')
        .style('stroke', 'red')
        .attr('d', valueline2(data));

      svg.append('g')     // Add the X Axis
        .attr('class', 'x axis')
        .attr('transform', 'translate(0,' + height + ')')
        .call(xAxis);

      svg.append('g')     // Add the Y Axis
        .attr('class', 'y axis')
        .call(yAxis);

      svg.append('text')
        .attr('transform', 'translate(' + (width + 3) + ',' + y(data[0].open) + ')')
        .attr('dy', '.35em')
        .attr('text-anchor', 'start')
        .style('fill', 'red')
        .text('Open');

      svg.append('text')
        .attr('transform', 'translate(' + (width + 3) + ',' + y(data[0].close) + ')')
        .attr('dy', '.35em')
        .attr('text-anchor', 'start')
        .style('fill', 'steelblue')
        .text('Close');

      console.log(data.length - 1);
      console.log(data[data.length - 1].open);
      console.log(data[0].open);
      console.log(y(data[0].open));
      console.log(y(data[0].close));

    }
  };
};
