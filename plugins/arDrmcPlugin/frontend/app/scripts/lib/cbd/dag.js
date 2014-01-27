(function () {

  'use strict';

  var d3 = require('d3');

  // Directed acyclic graph

  var graph = function (selection) {
    selection.each(function (data) {
      var svg = d3.select(this).selectAll('svg').data([data]);
      svg.enter()
        .append('svg')
        .append('g').attr('class', 'graph')
        .append('circle').attr({
          cx: 150,
          cy: 150,
          r: 100
        });
    });

    // This is where all the rendering heavy lifting should be happening, using
    // dagre-d3, etc...
  };

  module.exports = function () {
    return graph;
  };

})();
