(function () {

  'use strict';

  var Graph = require('./graph');
  var Zoom = require('./zoom');
  var d3 = require('d3');
  var dagreD3 = require('dagre-d3');

  function ContextBrowser (container, options) {
    options = options || {};

    this.container = container;
  }

  ContextBrowser.prototype.init = function (data) {
    // SVG layout
    this.rootSVG = d3.select(this.container.get(0)).append('svg');
    this.graphSVG = this.rootSVG.append('svg').attr({ 'class': 'graph-attach' });
    this.g = this.graphSVG.append('g');

    this.graph = new Graph(data);
    this.renderer = new dagreD3.Renderer();

    // Customize rendering, maybe class-inheritance later?
    var _drawNodes = this.renderer.drawNodes();
    this.renderer.drawNodes(function (graph, root) {
      var svgNodes = _drawNodes(graph, root);
      svgNodes.each(function (u) {
        console.log('rendering node', graph.node(u));
      });
      return svgNodes;
    });

    // Configure zoom
    new Zoom(this.rootSVG);

    this.draw();
  };

  ContextBrowser.prototype.draw = function () {
    var behavior = dagreD3.layout().nodeSep(20).rankSep(80).rankDir('RL');
    var layout = this.renderer.layout(behavior).run(this.graph, this.g);

    // Update the size of the SVG
    this.graphSVG.attr({
      'width': this.rootSVG.style('width'),
    });

    this.rootSVG.attr({
      'height': layout.graph().height + 40
    });
  };

  ContextBrowser.prototype.reset = function () {

  };

  ContextBrowser.prototype.showRelationships = function () {
    this.graphSVG.select('.edgePaths, .edgeLabels').style('display', 'inline');
  };

  ContextBrowser.prototype.hideRelationships = function () {
    this.graphSVG.select('.edgePaths, .edgeLabels').style('display', 'none');
  };

  module.exports = ContextBrowser;

})();
