(function () {

  'use strict';

  var angular = require('angular');
  var dagre = require('dagre');

  function Graph (data) {
    // Call parent constructor
    this._parent = dagre.Digraph.call(this);

    // Only if we are passing data (see BaseGraph.copy)
    if (data !== undefined) {
      this.data = data;
      this.build();
    }
  }

  Graph.prototype = Object.create(dagre.Digraph.prototype);
  Graph.prototype.constructor = Graph;

  Graph.prototype.build = function () {
    var self = this;

    var parseTree = function (tree, parent) {
      for (var i in tree) {
        var element = tree[i];
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
          parseTree(element.children, element);
        }
      }
    };

    // Add root node
    var root = this.data;
    self.addNode(root.id, {
      id: root.id,
      level: root.level,
      label: root.title
    });

    parseTree(this.data.children, root);
  };

  /**
   * I could be using .children if I move to CDigraph, but there's something
   * that it's blocking me from moving to that type of graph, can't remember
   * now. The following function mimics children() using predecessors.
   * Named "descendants" to avoid conflicts with BaseGraph.
   */
  Graph.prototype.descendants = function (u, options) {
    var self = this;
    var children = [];
    options = options || {};
    options.onlyId = typeof options.onlyId !== 'undefined' && options.onlyId === true;
    options.andSelf = typeof options.andSelf !== 'undefined' && options.andSelf === true;

    var push = function (u) {
      if (options.onlyId) {
        children.push(u);
      } else {
        var node = self.node(u);
        children.push(node);
      }
    };

    var walk = function (u) {
      var p = self.predecessors(u);
      if (p.length === 0) {
        return;
      }
      p.forEach(function (e) {
        push(e);
        walk(e);
      });
    };

    if (options.andSelf) {
      push(u);
    }

    walk(u);

    return children;
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

  /**
   * This method is like copy() (see BaseGraph), but ignores edges that are not
   * hierarchical.
   */
  Graph.prototype.copyHierarchicalGraph = function () {
    var copy = new this.constructor();
    copy.graph(this.graph());
    this.eachNode(function (u, value) {
      copy.addNode(u, value);
    });
    this.eachEdge(function (e, u, v, value) {
      // Copy only hierarchical edges
      if (value.type === undefined ||  value.type === 'hierarchical') {
        copy.addEdge(e, u, v, value);
      }
    });
    copy._nextId = this._nextId;
    return copy;
  };

  module.exports = Graph;

})();
