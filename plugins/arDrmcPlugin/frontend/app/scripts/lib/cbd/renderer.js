(function () {

  'use strict';

  var d3 = require('d3');
  var dagreD3 = require('dagre-d3');

  var _drawNodes;
  var _drawEdgePaths;
  var _postRender;
  var _transition;

  // TODO: overriding dagreD3 is getting messy, just copy and reuse?

  function Renderer () {
    this.renderer = new dagreD3.Renderer();

    // Override drawNodes
    _drawNodes = this.renderer.drawNodes();
    this.renderer.drawNodes(drawNodes);

    // Override drawEdgePaths
    _drawEdgePaths = this.renderer.drawEdgePaths();
    this.renderer.drawEdgePaths(drawEdgePaths);

    // Override postRender
    _postRender = this.renderer.postRender();
    this.renderer.postRender(postRender);

    _transition = this.renderer.transition();

    return this.renderer;
  }

  function drawNodes (g, root) {
    var svgNodes = _drawNodes(g, root);
    svgNodes.each(function (u) {
      var node = d3.select(this);
      var r = node.select('rect').attr('class', 'content');

      node.classed('level-' + g.node(u).level, true);

      // Background effect
      node
        .insert('rect', 'rect.content')
        .attr({
          'class': 'background',
          'x': r.attr('x'),
          'y': r.attr('y'),
          'rx': r.attr('rx'),
          'ry': r.attr('ry'),
          'width': r.attr('width'),
          'height': r.attr('height')
        });

    });
    return svgNodes;
  }

  function drawEdgePaths (g, root) {
    return _drawEdgePaths(g, root).selectAll('path').attr('marker-end', '');
  }

  function postRender (g, root) {
    console.log(g, root);
  }

  module.exports = Renderer;

})();
