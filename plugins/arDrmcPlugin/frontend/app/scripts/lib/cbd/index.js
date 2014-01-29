(function () {

  'use strict';

  var Graph = require('./graph');
  var Zoom = require('./zoom');
  var d3 = require('d3');
  var dagreD3 = require('dagre-d3');

  function ContextBrowser (container, data, options) {
    options = options || {};

    this.container = container;

    // SVG layout
    this.rootSVG = d3.select(this.container.get(0)).append('svg');
    this.graphSVG = this.rootSVG.append('svg').attr({ 'width': '100%', 'height': '100%', 'class': 'graph-attach'});
    this.g = this.graphSVG.append('g');

    this.graph = new Graph(data);
    this.renderer = new dagreD3.Renderer();

    new Zoom(this.graphSVG);

    this.draw();

    this.reset();
  }

  ContextBrowser.prototype.draw = function () {
    var layout = dagreD3.layout().nodeSep(20).rankSep(80).rankDir('RL');
    this.renderer.layout(layout).run(this.graph, this.g);
  };

  ContextBrowser.prototype.reset = function () {

  };

  module.exports = ContextBrowser;

})();
