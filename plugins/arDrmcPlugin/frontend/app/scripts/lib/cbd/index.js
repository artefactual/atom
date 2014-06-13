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

  ContextBrowser.prototype.init = function (data, filter) {

    // Main SVG
    this.rootSVG = d3.select(this.container.get(0))
      .append('svg')
      .attr({
        'height': '100%',
        'width': '100%',
        'class': 'graph-root'
      });

    // Is this needed?
    this.graphSVG = this.rootSVG.append('svg').attr('class', 'graph-attach');

    // Container element for pan/zoom
    this.groupSVG = this.graphSVG.append('g');

    this.graph = new Graph(data);
    this.renderer = new Renderer();

    if (typeof filter === 'function') {
      this.graph.nodes().forEach(function (u) {
        filter.call(this, u);
      });
    }

    this.draw();

    // Configure zoom
    this.zoom = new Zoom(this.rootSVG);
    this.setupEvents();

  };

  ContextBrowser.prototype.draw = function () {
    this.renderer.run(this.graph, this.groupSVG);
  };

  ContextBrowser.prototype.changeRankingDirection = function (rankDir) {
    this.renderer.rankDir = rankDir;
    this.draw();
    this.center();
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
      var target = d3.event.target.correspondingUseElement ? d3.event.target.correspondingUseElement : d3.event.target;
      if ($this.has(target)) {
        var node = jQuery(target).closest('.node').get(0);
        fn.call(
          // Context: node (this)
          node,
          // Param 1: context browser
          cb,
          // Param 2: datum
          d3.select(node).datum(),
          // Param 3: index (REMOVE?)
          0
        );
      }
    };

    this.rootSVG.on('click', jQuery.proxy(this.clickSVG, this));

    this.graphSVG.select('.nodes')
      .on('click', jQuery.proxy(nodeFilter, null, this.clickNode))
      .on('mouseover', jQuery.proxy(nodeFilter, null, this.hoverNode))
      .on('mouseout', jQuery.proxy(nodeFilter, null, this.hoverNode));

    this.graphSVG.select('.edgePaths')
      .on('click', jQuery.proxy(this.clickPath, this));

    this.graphSVG.select('.expandCollapseIcons')
      .on('click', jQuery.proxy(this.clickExpandCollapseIcon, this));

    this.graphSVG.select('.supportingTechnologyIcons')
      .on('click', jQuery.proxy(this.clickSupportingTechnologyIcon, this));
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

  ContextBrowser.prototype.collapse = function (datum, collapsed) {
    var node = this.graph.node(datum);
    if (!node.collapsible) {
      return;
    }
    node.collapsed = typeof collapsed !== 'undefined' ? collapsed : !node.collapsed;
    var status = node.collapsed;
    this.graph.descendants(datum, { andSelf: false }).forEach(function (element) {
      element.hidden = status;
      element.collapsed = false;
    });
    this.draw();
  };

  ContextBrowser.prototype.clickExpandCollapseIcon = function () {
    var target = d3.event.target.correspondingUseElement ? d3.event.target.correspondingUseElement : d3.event.target;
    var jg = jQuery(target).closest('g');
    if (!jg.length) {
      return;
    }
    var id = d3.select(target).datum();
    this.collapse(id);
  };

  ContextBrowser.prototype.clickSupportingTechnologyIcon = function () {
    var target = d3.event.target.correspondingUseElement ? d3.event.target.correspondingUseElement : d3.event.target;
    var jg = jQuery(target).closest('g');
    if (!jg.length || d3.select(jg.get(0)).classed('nested-supporting-technologies')) {
      return;
    }
    var id = d3.select(target).datum();
    this.events.emitEvent('click-supporting-technology-icon', [{ id: id }]);
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
    var shiftKey = d3.event ? d3.event.shiftKey : false;
    var target = d3.event ? d3.event.target : undefined;
    if (!n.classed('active')) {
      if (!shiftKey) {
        context.graphSVG.selectAll('.node.active').each(function (datum, index) {
          d3.select(this).classed('active', false);
          if (target !== undefined) {
            context.events.emitEvent('unpin-node', [{ id: datum, index: index }, target]);
          }
        });
      }
      n.classed('active', true);
      if (target !== undefined) {
        context.events.emitEvent('pin-node', [{ id: datum, index: index }, target]);
      }
    } else {
      n.classed('active', false);
      if (target !== undefined) {
        context.events.emitEvent('unpin-node', [{ id: datum, index: index }, target]);
      }
    }
  };

  ContextBrowser.prototype.clickPath = function () {
    var target = d3.event.target;
    var jg = jQuery(target).closest('g');
    if (!jg.length) {
      return;
    }
    var datum = d3.select(target).datum();
    var edge = this.graph.edge(datum);
    this.events.emitEvent('click-path', [{ id: datum, edge: edge, event: d3.event }, target]);
  };

  ContextBrowser.prototype.clickSVG = function () {
    var target = d3.event.target.correspondingUseElement ? d3.event.target.correspondingUseElement : d3.event.target;
    var n = d3.select(target);
    // I have no idea why!
    if (typeof n.getAttribute === 'undefined') {
      return;
    }
    if (n.classed('graph-root')) {
      this.events.emitEvent('click-background');
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

  ContextBrowser.prototype.selectRootNode = function () {
    var node = this.graphSVG.select('.node');
    this.clickNode.call(node.node(), this, node.datum(), 0);
  };

  // Graph contents manipulation

  ContextBrowser.prototype.addNode = function (id, label, level, parentId) {
    if (typeof level === 'undefined') {
      level = 'description';
    }
    this.graph.addNode(id, {
      id: id,
      level: level,
      label: label
    });
    this.graph.addEdge(id + ':' + parentId, id, parentId);
    this.graph.updateCollapsible(parentId, false);
    this.draw();
  };

  ContextBrowser.prototype.deleteNodes = function (nodes) {
    var self = this;
    nodes.forEach(function (u) {
      var parents = self.graph.successors(u);
      self.graph.delNode(u);
      self.graph.updateCollapsible(parents[0]);
    });
    this.draw();
  };

  /**
   * Expects an array of nodes where each member doesn't include any of the
   * other nodes between its ancestors (different subtrees?).
   */
  ContextBrowser.prototype.moveNodes = function (nodes, target) {
    var self = this;
    if (!(nodes instanceof Array)) {
      nodes = [nodes];
    }
    nodes.forEach(function (u) {
      var edges = self.graph.outEdges(u);
      if (edges.length !== 1) {
        return false;
      }
      // Remember parentId before we move
      var parentId = null;
      var successors = self.graph.successors(u);
      if (successors.length > 0) {
        parentId = successors[0];
      }
      // Update edges
      self.graph.delEdge(edges.pop());
      self.graph.addEdge(u + ':' + target, u, target);
      // Update collapsibles
      if (parentId !== null) {
        self.graph.updateCollapsible(parentId);
      }
    });
    self.graph.updateCollapsible(target);
    this.draw();
  };

  ContextBrowser.prototype.toggleNodesVisibility = function (filter, value) {
    var self = this;
    var changed = false;
    this.graph.nodes().forEach(function (u) {
      var node = self.graph.node(u);
      if (filter.call(this, node)) {
        node.hidden = !value;
        changed = true;
      }
    });
    if (changed) {
      this.draw();
    }
  };

  ContextBrowser.prototype.cancelNodeSelection = function (selection) {
    var nodes = this.graphSVG.selectAll('.node');
    if (typeof selection !== 'undefined') {
      nodes = nodes.data(selection, function (d) { return d; });
    }
    nodes
      .classed('disabled', false)
      .attr('style', 'opacity: 1;');
  };

  // It would be nice to use promises here
  ContextBrowser.prototype.promptNodeSelection = function (options) {
    var self = this;

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
      // Make sure that we are not allowing an excluded item
      if (exclusionList.indexOf(id) > -1) {
        self.cancelNodeSelection(exclusionList);
        return;
      }
      // Invoke callback
      options.action.call(null, id);
    });
  };

  ContextBrowser.prototype.createAssociativeRelationship = function (relation_id, source, target, type) {
    this.graph.addAssociativeEdge(relation_id, source, target, type);
    this.draw();
  };

  module.exports = ContextBrowser;

})();
