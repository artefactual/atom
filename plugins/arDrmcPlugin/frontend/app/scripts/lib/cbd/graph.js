(function () {

  'use strict';

  var dagreD3 = require('dagre-d3');

  function Graph (data) {
    this.data = data;

    dagreD3.Digraph.call(this);

    this.build();
  }

  Graph.prototype = Object.create(dagreD3.Digraph.prototype);
  Graph.prototype.constructor = Graph;

  Graph.prototype.build = function () {
    var self = this;
    // Iterate over nodes
    this.data.forEach(function (element, index, array) {
      console.log(element, index, array);
      self.addNode(1);
      /*
        this.addNode(element.id, {
          id: element.id,
          // width: self.defaultBoxSize.width,
          // height: self.defaultBoxSize.height,
          level: element.level,
          title: element.title
        });
      */
    });
  };

  module.exports = Graph;

})();
