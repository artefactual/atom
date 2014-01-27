(function () {

  'use strict';

  var Node = require('./node');
  var Graph = require('./graph');

  module.exports.createGraph = function (data) {
    console.log('createGraph, data received:', data);
    var nodes = {};
    var graph = new Graph();
    for (var i = 0; i < 10; i++) {
      nodes[i] = new Node(i);
    }
    for (var id in nodes) {
      graph.addNode(nodes[id]);
    }
    return graph;
  };

  module.exports.values = function (object) {
    return Object.keys(object).map(function (key) {
      return object[key];
    });
  };

})();
