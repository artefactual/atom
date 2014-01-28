(function () {

  'use strict';

  // var utils = require('./utils');
  var Graph = require('./graph');
  var d3 = require('d3');

  function ContextBrowser (container, data, options) {
    options = options || {};

    this.container = container;

    this.rootSVG = d3.select(this.container.get(0)).append('svg');
    this.graphSVG = this.rootSVG.append('svg').attr({
      'width': '100%',
      'height': '100%',
      'border': '1px solid #333',
      'class': 'graph-attach'
    });

    this.graph = new Graph(data);

    this.draw();

    this.reset();
  }

  ContextBrowser.prototype.draw = function () {
    // this.graphSVG.datum(this.graph).call(this.dag);
  };

  ContextBrowser.prototype.reset = function () {

  };

  module.exports = ContextBrowser;

})();
