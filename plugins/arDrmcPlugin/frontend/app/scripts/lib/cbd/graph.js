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

  Graph.prototype.filter = function (u, filterFn) {
    var self = this;
    var list = [];
    var go = function (u) {
      var p = self.predecessors(u);
      if (p.length === 0) {
        return;
      }
      p.forEach(function (e) {
        var node = self.node(e);
        if (angular.isFunction(filterFn)) {
          if (filterFn.call(null, node)) {
            list.push(node);
          }
        } else {
          list.push(node);
        }
        go(e);
      });
    };

    go(u);

    return list;
  };

  module.exports = Graph;

})();
