'use strict';

var myd3 = require('d3');

module.exports = function () {
  return {
    restrict: 'E',
    link: function (scope, element) {
      // the D3 bits...
      var color = myd3.scale.category10();
      var width = 100;
      var height = 100;
      var pie = myd3.layout.pie().sort(null);
      var arc = myd3.svg.arc()
        .outerRadius(width / 2 * 0.9);
      var data = [12,42,53,12];
      var svg = myd3.select(element[0]).append('svg')
        .attr ({width: width, height: height})
        .append ('g')
        .attr ('transform', 'translate(' + width / 2 + ',' + height / 2 + ')');
      // add the <path>s for each arc slice
      svg.selectAll('path').data(pie(data)) // our data
        .enter().append('path')
        .style ('stroke', 'white')
        .attr ('d', arc)
        .attr ('fill', function (d, i) {
          return color(i);
        });
    },
    scope: {
    }
  };
};
