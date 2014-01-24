(function () {

  'use strict';

  function Node (id) {

    this.id = id;

    this.neverVisible = false;
    this.hidden = false;

    // The immediate child nodes in the graph, regardless of visibility
    this.childNodes = {};

    // The immediate parent nodes in the graph, regardless of visibility
    this.parentNodes = {};

  }

  module.exports = {
    Node: Node
  };

})();
