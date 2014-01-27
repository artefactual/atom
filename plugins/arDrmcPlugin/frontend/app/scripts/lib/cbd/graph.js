(function () {

  'use strict';

  require('./node');

  function Graph () {
    this.nodeList = [];
    this.nodes = {};
  }

  Graph.prototype.addNode = function (node) {
    this.nodeList.push(node);
    this.nodes[node.id] = node;
  };

  Graph.prototype.getNode = function (id) {
    return this.nodes[id];
  };

  Graph.prototype.getNodes = function () {
    return this.nodeList;
  };

  Graph.prototype.getVisibleNodes = function () {
    return this.nodeList.filter(function (node) {
      return node.visible();
    });
  };

  Graph.prototype.getVisibleLinks = function () {
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
          visibleParentMap[node.id][pid] = true;
        } else {
          exploreNode(parent);
          var grandparents = visibleParentMap[pid];
          for (var gpid in grandparents) {
            visibleParentMap[node.id][gpid] = true;
          }
        }
      }
    };

    for (var i = 0; i < this.nodeList.length; i++) {
      exploreNode(this.nodeList[i]);
    }

    var nodes = this.nodes;
    var ret = [];
    var visibleNodes = this.getVisibleNodes();

    var pushPid = function (pid) {
      ret.push({
        source: nodes[pid],
        target: node
      });
    };

    for (var j = 0; j < visibleNodes.length; j++) {
      var node = visibleNodes[j];
      var parentids = visibleParentMap[node.id];
      Object.keys(parentids).forEach(pushPid);
    }

    return ret;
  };

  module.exports = Graph;

})();
