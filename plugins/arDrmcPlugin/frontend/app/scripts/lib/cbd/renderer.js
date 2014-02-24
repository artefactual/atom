(function () {

  'use strict';

  var d3 = require('d3');
  var dagre = require('dagre');

  function Renderer () {
    this.layout = dagre.layout().nodeSep(20).rankSep(80).rankDir('RL');
    this.edgeInterpolate = 'bundle';
    this.edgeTension = 0.95;
  }

  Renderer.prototype.run = function (graph, svg) {
    // First copy the input graph so that it is not changed by the rendering
    // process.
    graph = copyAndInitGraph(graph);

    // Create layers
    svg
      .selectAll('g.edgePaths, g.edgeLabels, g.nodes')
      .data(['edgePaths', 'edgeLabels', 'nodes'])
      .enter()
        .append('g')
        .attr('class', function (d) {
          return d;
        });

    // Create node and edge roots, attach labels, and capture dimension
    // information for use with layout.
    var svgNodes = drawNodes(graph, svg.select('g.nodes'));
    var svgEdgeLabels = drawEdgeLabels(graph, svg.select('g.edgeLabels'));

    svgNodes.each(function (u) {
      calculateDimensions(this, graph.node(u));
    });
    svgEdgeLabels.each(function (e) {
      calculateDimensions(this, graph.edge(e));
    });

    // Now apply the layout function
    var result = runLayout(graph, this.layout);

    var svgEdgePaths = drawEdgePaths(graph, svg.select('g.edgePaths'));

    // Apply the layout information to the graph
    positionNodes(result, svgNodes);
    positionEdgeLabels(result, svgEdgeLabels);
    positionEdgePaths(result, svgEdgePaths, this.edgeTension, this.edgeInterpolate);

    postRender(result, svg);

    return result;
  };

  function runLayout (graph, layout) {
    // Pass to dagre a copy of the graph discarding every edge that is not
    // hierarchical. We want the layout to be defined only by hierarchical edges
    // See https://github.com/cpettitt/dagre/issues/110
    var result = layout.run(graph.copyHierarchicalGraph());

    // Re-add associative edges and define a control point
    // That point will be used later by positionEdgePaths
    graph.eachEdge(function (e, u, v, value) {
      if (value.type === 'associative') {
        var src = result.node(u);
        var tgt = result.node(v);
        value.points = [
          findMidPoint([
            { x: src.x + 100, y: src.y },
            { x: tgt.x + 100, y: tgt.y }
          ])
        ];
        result.addEdge(e, u, v, value);
      }
    });

    // Copy labels to the result graph
    graph.eachNode(function (u, value) {
      result.node(u).label = value.label;
    });
    graph.eachEdge(function (e, u, v, value) {
      result.edge(e).label = value.label;
    });

    return result;
  }

  function drawNodes (g, root) {
    var nodes = g.nodes().filter(function (u) {
      return !isComposite(g, u);
    });

    var svgNodes = root
      .selectAll('g.node')
      .classed('enter', false)
      .data(nodes, function (u) {
        return u;
      });

    svgNodes.selectAll('*').remove();

    svgNodes
      .enter()
        .append('g')
          .style('opacity', 0)
          .attr('class', function (u) {
            var classes = ['node', 'enter'];
            classes.push('level-' + g.node(u).level);
            return classes.join(' ');
          });

    svgNodes.each(function (u) {
      var n = d3.select(this);
      // Add label
      addLabel(g.node(u), n, 10, 10);
      // Add .content class
      var r = n.select('rect').classed('content', true);
      // Insert background
      n.insert('rect', 'rect.content').attr({
        'class': 'background',
        'x': r.attr('x'),
        'y': r.attr('y'),
        'rx': r.attr('rx'),
        'ry': r.attr('ry'),
        'width': r.attr('width'),
        'height': r.attr('height')
      });
      // Insert expand button
      // n.insert('use').attr('xlink:href', '#expand-icon');
    });

    transition(svgNodes.exit())
      .style('opacity', 0)
      .remove();

    return svgNodes;
  }

  function drawEdgeLabels (g, root) {
    var svgEdgeLabels = root
      .selectAll('g.edgeLabel')
      .classed('enter', false)
      .data(g.edges(), function (e) {
        return e;
      });

    svgEdgeLabels.selectAll('*').remove();

    svgEdgeLabels
      .enter()
        .append('g')
          .style('opacity', 0)
          .attr('class', 'edgeLabel enter');

    svgEdgeLabels.each(function (e) {
      addLabel(g.edge(e), d3.select(this), 0, 0);
    });

    transition(svgEdgeLabels.exit())
      .style('opacity', 0)
      .remove();

    return svgEdgeLabels;
  }

  function drawEdgePaths (g, root) {
    var svgEdgePaths = root
      .selectAll('g.edgePath')
      .classed('enter', false)
      .data(g.edges(), function (e) {
        return e;
      });

    svgEdgePaths
      .enter()
        .append('g')
          .attr('class', function (e) {
            var classes = ['edgePath', 'enter'];
            var edge = g.edge(e);
            if (typeof edge.type !== 'undefined') {
              classes.push('r-' + edge.type);
            }
            return classes.join(' ');
          })
          .append('path')
            .style('opacity', 0);
            // .attr('marker-end', 'url(#arrowhead)');

    transition(svgEdgePaths.exit())
      .style('opacity', 0)
      .remove();

    return svgEdgePaths;
  }

  function positionNodes (g, svgNodes) {
    function transform (u) {
      var value = g.node(u);
      return 'translate(' + value.x + ',' + value.y + ')';
    }

    // For entering nodes, position immediately without transition
    svgNodes.filter('.enter').attr('transform', transform);

    transition(svgNodes)
      .style('opacity', 1)
      .attr('transform', transform);
  }

  function positionEdgeLabels (g, svgEdgeLabels) {
    function transform (e) {
      var value = g.edge(e);
      var point = findMidPoint(value.points);
      return 'translate(' + point.x + ',' + point.y + ')';
    }

    // For entering edge labels, position immediately without transition
    svgEdgeLabels.filter('.enter').attr('transform', transform);

    transition(svgEdgeLabels)
      .style('opacity', 1)
      .attr('transform', transform);
  }

  function positionEdgePaths (g, svgEdgePaths, edgeTension, edgeInterpolate) {
    var interpolate = edgeInterpolate;
    var tension = edgeTension;

    function calcPoints (e) {
      var value = g.edge(e);
      var source = g.node(g.incidentNodes(e)[0]);
      var target = g.node(g.incidentNodes(e)[1]);

      var points, p0, p1;

      // For associative relationships
      if (typeof value.type !== 'undefined' && value.type === 'associative') {
        points = value.points.slice();
        points.unshift(sideMiddlePoint(source));
        points.push(sideMiddlePoint(target));
      // For hierarchical relationships
      } else {
        points = value.points.slice();
        p0 = points.length === 0 ? target : points[0];
        p1 = points.length === 0 ? source : points[points.length - 1];

        points.unshift(intersectRect(source, p0));
        points.push(intersectRect(target, p1)); // TODO: use bpodgursky's shortening algorithm here
      }

      return d3.svg.line()
        .x(function (d) {
          return d.x;
        })
        .y(function (d) {
          return d.y;
        })
        .interpolate(interpolate)
        .tension(tension)
        (points);
    }

    svgEdgePaths.filter('.enter').selectAll('path')
      .attr('d', calcPoints);

    transition(svgEdgePaths.filter('.enter').selectAll('path'))
      .style('opacity', 1);

    // When initiating a transition with 1 more control point than previously
    // present, the transition immediately sets the terminal control point to the
    // final position. This hack inserts an artificial control point. It's not
    // pretty, but better than the previous behavior.
    svgEdgePaths.selectAll('path')
        .filter(function (e) {
          var points = g.edge(e).points;
          return d3.select(this).attr('d').split('C').length - 2 < points.length;
        })
        .attr('d', function () {
          var dSplit = d3.select(this).attr('d').split('C');
          dSplit.splice(1, 0, dSplit[1]);
          return dSplit.join('C');
        });

    transition(svgEdgePaths.filter(':not(.enter)').selectAll('path'))
      .attr('d', calcPoints);
  }

  // By default we do not use transitions
  function transition (selection) {
    return selection;
  }

  function isComposite (g, u) {
    return 'children' in g && g.children(u).length;
  }

  function postRender (graph, root) {
    var defs = root.append('svg:defs');

    if (graph.isDirected() && root.select('#arrowhead').empty()) {
      defs.append('svg:marker')
        .attr('id', 'arrowhead')
        .attr('viewBox', '0 0 10 10')
        .attr('refX', 8)
        .attr('refY', 5)
        .attr('markerUnits', 'strokewidth')
        .attr('markerWidth', 8)
        .attr('markerHeight', 5)
        .attr('orient', 'auto')
        .attr('style', 'fill: #333')
        .append('svg:path')
          .attr('d', 'M 0 0 L 10 5 L 0 10 z');
    }

    var collapseIcon = defs.append('svg:g').attr('id', 'collapse-icon').append('svg:g');
    collapseIcon.append('rect').attr({ x: 1, y: 6, fill: 'none', stroke: '#ffffff', 'stroke-width': 3, width: 16, height: 6 });
    collapseIcon.append('rect').attr({ x: 1, y: 6, fill: '#999999', stroke: 'none', 'stroke-width': 3, width: 16, height: 6 });

    var expandIcon = defs.append('svg:g').attr('id', 'expand-icon').append('svg:g');
    expandIcon.append('polygon').attr({ fill: 'none', stroke: '#ffffff', 'stroke-width': 3, points: '6,17 6,12 1,12 1,6 6,6 6,1 12,1 12,6 17,6 17,12 12,12 12,17' });
    expandIcon.append('polygon').attr({ fill: '#999999', stroke: 'none', points: '6,17 6,12 1,12 1,6 6,6 6,1 12,1 12,6 17,6 17,12 12,12 12,17' });
  }

  function addLabel (node, root, marginX, marginY) {
    // Add the rect first so that it appears behind the label
    var label = node.label;
    var rect = root.append('rect');
    var labelSvg = root.append('g');

    if (label[0] === '<') {
      addForeignObjectLabel(label, labelSvg);
      // No margin for HTML elements
      marginX = marginY = 0;
    } else {
      addTextLabel(label, labelSvg, Math.floor(node.labelCols), node.labelCut);
    }

    var bbox = root.node().getBBox();

    labelSvg.attr('transform', 'translate(' + (bbox.width / -2) + ',' + (bbox.height / -2) + ')');

    rect
      .attr('rx', 5)
      .attr('ry', 5)
      .attr('x', -(bbox.width / 2 + marginX))
      .attr('y', -(bbox.height / 2 + marginY))
      .attr('width', bbox.width + 2 * marginX)
      .attr('height', bbox.height + 2 * marginY);
  }

  function calculateDimensions (group, value) {
    var bbox = group.getBBox();
    value.width = bbox.width;
    value.height = bbox.height;
  }

  function copyAndInitGraph (graph) {
    var copy = graph.copy();

    // Init labels if they were not present in the source graph
    copy.nodes().forEach(function (u) {
      var value = copy.node(u);
      if (value === undefined) {
        value = {};
        copy.node(u, value);
      }
      if (!('label' in value)) {
        value.label = '';
      }
    });

    copy.edges().forEach(function (e) {
      var value = copy.edge(e);
      if (value === undefined) {
        value = {};
        copy.edge(e, value);
      }
      if (!('label' in value)) {
        value.label = '';
      }
    });

    return copy;
  }

  function addForeignObjectLabel (label, root) {
    var fo = root
      .append('foreignObject')
        .attr('width', '100000');

    var w, h;
    fo
      .append('xhtml:div')
        .style('float', 'left')
        // TODO find a better way to get dimensions for foreignObjects...
        .html(function () {
          return label;
        })
        .each(function () {
          w = this.clientWidth;
          h = this.clientHeight;
        });

    fo
      .attr('width', w)
      .attr('height', h);
  }

  function addTextLabel (label, root, labelCols, labelCut) {
    if (labelCut === undefined) {
      labelCut = 'false';
    }
    labelCut = labelCut.toString().toLowerCase() === 'true';

    var node = root
      .append('text')
      .attr('text-anchor', 'left');

    label = label.replace(/\\n/g, '\n');

    var arr = labelCols ? wordwrap(label, labelCols, labelCut) : label;
    arr = arr.split('\n');
    for (var i = 0; i < arr.length; i++) {
      node
        .append('tspan')
          .attr('dy', '1em')
          .attr('x', '1')
          .text(arr[i]);
    }
  }

  // Thanks to
  // http://james.padolsey.com/javascript/wordwrap-for-javascript/
  function wordwrap (str, width, cut, brk) {
    brk = brk || '\n';
    width = width || 75;
    cut = cut || false;

    if (!str) {
      return str;
    }

    var regex = '.{1,' + width + '}(\\s|$)' + (cut ? '|.{' + width + '}|.+$' : '|\\S+?(\\s|$)');

    return str.match(new RegExp(regex, 'g')).join(brk);
  }

  function findMidPoint (points) {
    var midIdx = points.length / 2;
    if (points.length % 2) {
      return points[Math.floor(midIdx)];
    } else {
      var p0 = points[midIdx - 1];
      var p1 = points[midIdx];
      return {x: (p0.x + p1.x) / 2, y: (p0.y + p1.y) / 2};
    }
  }

  function sideMiddlePoint (rect) {
    var ex = rect.x + rect.width / 2;
    var ey = rect.y;
    return {
      x: ex,
      y: ey
    };
  }

  function intersectRect (rect, point) {
    var x = rect.x;
    var y = rect.y;

    // For now we only support rectangles

    // Rectangle intersection algorithm from:
    // http://math.stackexchange.com/questions/108113/find-edge-between-two-boxes
    var dx = point.x - x;
    var dy = point.y - y;
    var w = rect.width / 2;
    var h = rect.height / 2;

    var sx, sy;
    if (Math.abs(dy) * w > Math.abs(dx) * h) {
      // Intersection is top or bottom of rect.
      if (dy < 0) {
        h = -h;
      }
      sx = dy === 0 ? 0 : h * dx / dy;
      sy = h;
    } else {
      // Intersection is left or right of rect.
      if (dx < 0) {
        w = -w;
      }
      sx = w;
      sy = dx === 0 ? 0 : w * dy / dx;
    }

    return {
      x: x + sx,
      y: y + sy
    };
  }

  module.exports = Renderer;

})();
