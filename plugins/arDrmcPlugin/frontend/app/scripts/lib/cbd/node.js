(function () {

  'use strict';

  var utils = require('./utils');

  function Node (id) {
    this.id = id;

    this.neverVisible = false;
    this.hidden = false;

    // The immediate child nodes in the graph, regardless of visibility
    this.childNodes = {};

    // The immediate parent nodes in the graph, regardless of visibility
    this.parentNodes = {};
  }

  Node.prototype.visible = function (_) {
    if (arguments.length === 0) {
      return (!this.neverVisible && !this.hidden);
    }

    this.hidden = !_;

    return this;
  };

  Node.prototype.addChild = function (child) {
    this.childNodes[child.id] = child;
  };

  Node.prototype.addParent = function (parent) {
    this.parentNodes[parent.id] = parent;
  };

  Node.prototype.removeChild = function (child) {
    if (child.id in this.childNodes) {
      delete this.childNodes[child.id];
    }
  };

  Node.prototype.removeParent = function (parent) {
    if (parent.id in this.parentNodes) {
      delete this.parentNodes[parent.id];
    }
  };

  Node.prototype.getParents = function () {
    return utils.values(this.parentNodes);
  };

  Node.prototype.getChildren = function () {
    return utils.values(this.childNodes);
  };

  Node.prototype.getVisibleParents = function () {
    var visibleParentMap = {};

    var exploreNode = function (node) {
      if (visibleParentMap[node.id]) {
        return;
      }
      visibleParentMap[node.id] = {};
      var parents = node.parentNodes;
      for (var pid in parents) {
        var parent = parents[pid];
        if (parent.visible()) {
          visibleParentMap[node.id][pid] = parent;
        } else {
          exploreNode(parent);
          var grandparents = visibleParentMap[pid];
          for (var gpid in grandparents) {
            visibleParentMap[node.id][gpid] = grandparents[gpid];
          }
        }
      }
    };

    exploreNode(this);

    return utils.values(visibleParentMap[this.id]);
  };

  module.exports = Node;

})();
