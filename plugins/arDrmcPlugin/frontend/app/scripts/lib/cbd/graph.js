(function () {

  'use strict';

  var angular = require('angular');
  var dagreD3 = require('dagre-d3');

  function Graph (data) {
    // Call parent constructor
    this._parent = dagreD3.Digraph.call(this);

    // Only if we are passing data (see BaseGraph.copy)
    if (data !== undefined) {
      this.data = data;
      this.build();
    }
  }

  Graph.prototype = Object.create(dagreD3.Digraph.prototype);
  Graph.prototype.constructor = Graph;

  Graph.prototype.build = function () {
    var self = this;

    var add = function (element, index, array, parent) {
      // Add node
      self.addNode(element.id, {
        id: element.id,
        width: 1, // self.defaultBoxSize.width,
        height: 2, // self.defaultBoxSize.height,
        level: element.level,
        label: element.title
      });

      // Add relation
      if (parent !== undefined) {
        var edgeId = element.id + ':' + parent.id;
        self.addEdge(edgeId, element.id, parent.id, { type: 'hierarchical' });
      }

      // Keep exploring down in the hierarchy
      if (angular.isArray(element.children)) {
        element.children.forEach(function (e, i, a) {
          add(e, i, a, element);
        });
      }
    };

    this.data.forEach(add);
  };

  module.exports = Graph;

})();
