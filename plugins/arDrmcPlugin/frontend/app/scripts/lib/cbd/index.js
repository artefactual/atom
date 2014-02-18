(function () {

  'use strict';

  var Graph = require('./graph');
  var Zoom = require('./zoom');
  var Renderer = require('./renderer');

  var d3 = require('d3');
  var jQuery = require('jquery');
  var EventEmitter = require('wolfy87-eventemitter');

  function ContextBrowser (container) {
    this.container = container;
    this.events = new EventEmitter();
  }

  ContextBrowser.prototype.init = function (data) {
    // SVG layout
    this.rootSVG = d3.select(this.container.get(0)).append('svg').attr('height', '100%').attr('class', 'graph-root');
    this.graphSVG = this.rootSVG.append('svg').attr('class', 'graph-attach');
    this.groupSVG = this.graphSVG.append('g');

    this.graph = new Graph(data);
    this.renderer = new Renderer();

    this.draw();

    // Configure zoom
    this.zoom = new Zoom(this.rootSVG);
    this.setupEvents();
  };

  ContextBrowser.prototype.draw = function () {
    this.renderer.run(this.graph, this.groupSVG);
  };

  ContextBrowser.prototype.center = function () {
    this.zoom.reset();
  };

  ContextBrowser.prototype.setupEvents = function () {
    // d3.selection.on doesn't support event delegation
    // Mimic it. Maybe I should just use jQuery.on() or setup events per node.
    var cb = this;
    var nodeFilter = function (fn) {
      var $this = jQuery(this);
      if ($this.has(d3.event.target)) {
        var node = jQuery(d3.event.target).closest('.node').get(0);
        fn.call(
          // Context: node (this)
          node,
          // Param 1: context browser
          cb,
          // Param 2: datum
          d3.select(node).datum()
        );
      }
    };

    this.rootSVG.on('click', this.clickSVG);

    this.graphSVG.select('.nodes')
      .on('click', jQuery.proxy(nodeFilter, null, this.clickNode))
      .on('mouseover', jQuery.proxy(nodeFilter, null, this.hoverNode))
      .on('mouseout', jQuery.proxy(nodeFilter, null, this.hoverNode));
  };

  ContextBrowser.prototype.showRelationships = function () {
    this.graphSVG.select('.edgePaths, .edgeLabels').style('display', 'inline');
  };

  ContextBrowser.prototype.hideRelationships = function () {
    this.graphSVG.select('.edgePaths, .edgeLabels').style('display', 'none');
  };

  ContextBrowser.prototype.unselectAll = function () {
    this.graphSVG.selectAll('.node').classed('active', false);
  };

  /**
   * Handler for click events. Allows selection of nodes by updating CSS classes
   * and firing events to a watcher.
   *
   * @this {object} - D3 g.node
   * @param {ContextBrowser} context
   * @param {string} datum - D3 element datum, e.g. context.graph.node(datum)
   * @param {number} index - Index
   */
  ContextBrowser.prototype.clickNode = function (context, datum, index) {
    var n = d3.select(this);
    if (!n.classed('active')) {
      if (!d3.event.shiftKey) {
        context.graphSVG.selectAll('.node.active').each(function (datum, index) {
          d3.select(this).classed('active', false);
          context.events.emitEvent('unpin-node', [{ id: datum, index: index }, d3.event.target]);
        });
      }
      n.classed('active', true);
      context.events.emitEvent('pin-node', [{ id: datum, index: index }, d3.event.target]);
    } else {
      n.classed('active', false);
      context.events.emitEvent('unpin-node', [{ id: datum, index: index }, d3.event.target]);
    }
  };

  ContextBrowser.prototype.clickSVG = function () {
    if (d3.select(d3.event.target).classed('graph-root')) {
      console.log('You have clicked the background!');
    }
  };

  /**
   * Handler for mouseover and mouseout events. Adds or remove the hover class.
   *
   * @this {object} - D3 g.node
   * @param {ContextBrowser} context
   * @param {string} datum - D3 element datum, e.g. context.graph.node(datum)
   * @param {number} index - Index
   */
  ContextBrowser.prototype.hoverNode = function () {
    if (d3.event.type === 'mouseover') {
      d3.select(this).classed('hover', true);
    } else if (d3.event.type === 'mouseout') {
      d3.select(this).classed('hover', false);
    }
  };

  // Graph contents manipulation

  ContextBrowser.prototype.addNode = function (id, label, level, parentId) {
    if (typeof level === 'undefined') {
      level = 'description';
    }
    this.graph.addNode(id, {
      id: Math.floor(Math.random() * 11),
      level: level,
      label: label
    });
    this.graph.addEdge(id + ':' + parentId, id, parentId);
    this.draw();
  };

  ContextBrowser.prototype.deleteNodes = function (nodes) {
    var self = this;
    nodes.forEach(function (element) {
      self.graph.delNode(element);
    });
    this.draw();
  };

  ContextBrowser.prototype.moveNodes = function (nodes, target) {
    var self = this;
    nodes.forEach(function (element) {
      var edges = self.graph.outEdges(element);
      if (edges.length !== 1) {
        return false;
      }
      self.graph.delEdge(edges.pop());
      self.graph.addEdge(element + ':' + target, element, target);
    });
    this.draw();
  };

  ContextBrowser.prototype.promptNodeSelection = function (options) {
    options = options || {};
    if (!options.hasOwnProperty('action')) {
      throw 'Missing action attribute (function callback)';
    }

    // Build exclusion list
    var exclusionList = [];
    if (options.hasOwnProperty('exclude')) {
      exclusionList = options.exclude;
    }

    // Disable excluded nodes
    this.graphSVG.selectAll('.node')
      .data(exclusionList, function (d) { return d; })
      .classed('disabled', true)
      .attr('style', 'opacity: 0.2;');

    // Add a only-once event handler
    var $nodes = jQuery(this.graphSVG.node());
    $nodes.one('click', '.node', function (event) {
      var $node = jQuery(event.target).closest('.node');
      var id = d3.select($node.get(0)).datum();
      options.action.call(null, id);
    });
  };

  ContextBrowser.prototype.createAssociativeRelationship = function (source, target, type) {
    for (var i in source) {
      var src = source[i];
      this.graph.addEdge(src + ':' + target, src, target, {
        type: 'associative'
      });
      this.draw();
      console.log(type);
    }
  };

  module.exports = ContextBrowser;

})();
