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
        var data = {
          id: element.id,
          level: element.level,
          label: element.title,
          collapsible: angular.isArray(element.children) && element.children.length > 0
        };
        if (element.hasOwnProperty('supporting_technologies_count')) {
          data.supporting_technologies_count = element.supporting_technologies_count;
        }
        if (element.hasOwnProperty('associations')) {
          data.associations = element.associations;
        }
        // Add node
        self.addNode(element.id, data);
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

    // Add associations now that all the edges and vectors have been added
    self.eachNode(function (n, e) {
      if (!e.hasOwnProperty('associations')) {
        return;
      }
      e.associations.forEach(function (u) {
        var edgeId = e.id + ':' + u.object_id + ':associative'; // u.id (relation.id)?
        self.addEdge(edgeId, e.id, u.object_id, {
          type: 'associative',
          relationId: u.id,
          typeId: u.type_id, // TODO: label: '...'
          objectId: u.object_id,
          subjectId: u.subject_id,
          constraint: false // TODO: https://github.com/cpettitt/dagre/issues/110
        });
      });
    });
  };

  Graph.prototype.updateCollapsible = function (u, collapsed) {
    var node = this.node(u);
    node.collapsible = this.predecessors(u).length > 0;
    node.collapsed = typeof collapsed !== 'undefined' ? collapsed : false;
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

  module.exports = Graph;

})();
