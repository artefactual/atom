'use strict';

function Plumb(element, scope)
{
  var self = this;

  this.element = element;

  this.scope = scope;

  this.levels = [
    { name: 'work' },
    { name: 'description' },
    { name: 'physical-component' },
    { name: 'digital-object' }
  ];

  this.defaultBoxSize = {
    width: 120,
    height: 14
  };

  this.nodeSep = 20;
  this.rankSep = 80;

  this.jsPlumbConfiguration = {

    // jsPlumb defaults
    defaults: {
      Container: this.element
    },

    // Types of relations
    connectors: {

      // Hierarchical
      hierarchical: {
        connector: 'Straight',
        anchors: ['Right', 'Left'],
        paintStyle: {
          lineWidth: 1,
          strokeStyle: '#cecece'
        },
        endpoint: 'Blank'
      },

      // Associative
      associative: {
        connector: [ 'Straight', { curviness: 50 }],
        anchors: ['Right', 'Right'],
        paintStyle: {
          lineWidth: 2,
          strokeStyle: 'rgb(131,8,135)',
          dashstyle: '1 1',
          joinstyle: 'miter'
        },
        endpoint: 'Dot',
        overlays: [['PlainArrow', { location: 1, width: 15, length: 12}]],
        label: 'is derivative of'
      }

    }
  };

  this.initialize = function(scope)
  {
    // Initialization
    console.log('plumb', 'Initializing...');

    // Create an instance of jsPlumb
    this.plumb = jsPlumb.getInstance();

    // Change jsPlumb.Defaults
    this.plumb.importDefaults(this.jsPlumbConfiguration.defaults);

    // Configure DOM listeners
    this.listen();

    // Build the directed graph using graphlib
    this.digraph = new dagre.Digraph();
    this.loadDataIntoDigraph();
  };

  this.listen = function()
  {
    this.element
      .on('click', jQuery.proxy(this.click, this));

    this.element.closest('.plumb-container').prev()
      .on('click', '.fullscreen', jQuery.proxy(this.toggleFullscreen, this))
      .on('click', '.add_child', jQuery.proxy(this.addChildNode, this));
  };

  this.addNodeIntoDigraph = function(node, isRoot)
  {
    self.digraph.addNode(node.id, {
      id: node.id,
      width: self.defaultBoxSize.width,
      height: self.defaultBoxSize.height,
      level: node.level,
      title: node.title
    });

    if (angular.isArray(node.children))
    {
      for (var i = 0; i < node.children.length; i++)
      {
        // Add children and partnership relation
        var child = self.addNodeIntoDigraph(node.children[i], false);
        self.addEdgeIntoDigraph(node.id, child.id, 'hierarchical');
      }
    }

    return node;
  };

  this.addEdgeIntoDigraph = function(sourceId, targetId, relationType)
  {
    var edgeId = sourceId + ':' + targetId;
    self.digraph.addEdge(edgeId, sourceId, targetId,
      // This is the object that we pass to digraph with some user-defined data
      {
        relationType: relationType
      });
  };

  this.loadDataIntoDigraph = function()
  {
    // Load collection
    for (var i = 0; i < this.scope.collection.length; i++)
    {
      self.addNodeIntoDigraph(this.scope.collection[i], true);
    }

    // Load relations
    if (this.scope.relations !== undefined)
    {
      for (var i = 0; i < this.scope.relations.length; i++)
      {
        self.addEdgeIntoDigraph(
          this.scope.relations[i].source,
          this.scope.relations[i].target,
          'associative');
      }
    }
  };

  this.updateWidgetSize = function()
  {
    var width = self.layout.graph().width;
    if (width < self.element.width())
    {
      width = self.element.width() - 10;
    }

    this.element.css({
      'width': width,
      'height': self.layout.graph().height + 60 });
  };

  this.computeLayout = function()
  {
    // Use dagre to build the layout by passing the digraph
    self.layout = dagre.layout().nodeSep(self.nodeSep).rankSep(self.rankSep).rankDir("LR").run(this.digraph);

    self.updateWidgetSize();
  };

  this.draw = function()
  {
    self.computeLayout();

    self.layout.eachNode(function(id, value) {
      self.renderNode(id, value);
    });

    self.layout.eachEdge(function(edgeId, sourceId, targetId, value) {
      self.renderEdge(edgeId, sourceId, targetId, value);
    });
  };

  /*
   * renderNode draws the new node using HTML
   */
  this.renderNode = function(id, value)
  {
    var node = this.digraph.node(id);

    var isRendered = node.el !== undefined;

    if (!isRendered)
    {
      var el = document.createElement('div');

      el.className = 'node node-level-' + node.level;
      el.id = 'node-' + id;
      el.setAttribute('data-id', id);
      el.innerHTML = node.title;
      el.style.position = 'absolute';
      el.style.width = this.defaultBoxSize.width + 'px';
      el.style.height = this.defaultBoxSize.height + 'px';
      el.style.lineHeight = this.defaultBoxSize.height + 'px';

      // Insert in DOM
      self.element[0].appendChild(el);

      // Configure drag-n-drop
      this.configureDragAndDrop(el);

      node.el = el;
    }
    else
    {
      var el = node.el;
    }

    el.style.left = value.x + 'px';
    el.style.top = value.y + 'px';
  };

  /*
   * renderEdge draws the connection between nodes using jsPlumb
   */
  this.renderEdge = function(edgeId, sourceId, targetId, value) {
    // The value var passed to this function by eachEdge doesn't include
    // user-defined data. We need to retrieve that by calling the edge() getter.
    var userValue = self.digraph.edge(edgeId);
    userValue.jsPlumbConnection = self.plumb.connect({
      source: this.digraph.node(sourceId).el,
      target: this.digraph.node(targetId).el,
    }, self.jsPlumbConfiguration.connectors[userValue.relationType]);
  };

  /*
   * configureDragAndDrop binds all the events needed to make drag-n-drop work
   */
  this.configureDragAndDrop = function(nodeEl)
  {
    if (nodeEl.jquery === undefined)
    {
      var nodeEl = jQuery(nodeEl);
    }

    // Make sure that we are not doing it twice
    if (nodeEl.data('drag-n-drop') === true)
    {
      return;
    }

    if (nodeEl.hasClass('node-level-work'))
    {
      return;
    }

    // Use jsPlumb draggable wrapper so jsPlumb can repaint edges
    this.plumb.draggable(nodeEl, {
      containment: self.element,
      start: function(event, ui) {
        ui.helper.data('originalZIndex', ui.helper.css('z-index'));
        ui.helper.css('z-index', 9999);
      },
      stop: function(event, ui) {
        ui.helper.css('z-index', ui.helper.data('originalZIndex') !== undefined ? ui.helper.data('originalZIndex') : 1);
      },
    });

    nodeEl.droppable({
      activeClass: 'droppable',
      hoverClass: 'droppable-hover',
      drop: function(event, ui) {
        var sourceEl = ui.helper;
        var targetEl = jQuery(event.target);
        self.moveNode(sourceEl, targetEl);
      }
    });

    // Mark this element configured
    nodeEl.data('drag-n-drop', true);
  };

  /* ------------------------------------------------------------------------
   * Interaction with nodes
   * ------------------------------------------------------------------------ */

  this.getNodes = function(relations)
  {
    return this.element.find('.node');
  };

  this.getActiveNode = function()
  {
    var activeNode = this.element.find('.node.active');
    if (!activeNode.length)
    {
      return;
    }

    return this.digraph.node(activeNode.data('id'));
  };

  this.activateNode = function(node)
  {
    var id = node.data('id');
    var aside = jQuery('#aside-id-' + id);

    if ($(node).hasClass('node-level-physical-component'))
    {
      return false;
    }

    if (node.hasClass('active'))
    {
      return;
    }

    this.deactivateAllNodes();
    node.addClass('active');

    if (aside.length)
    {
      aside.show();
    }
  };

  this.activateDefaultNode = function()
  {
    this.deactivateAllNodes();
    this.getNodes().filter('#node-0').addClass('active');
    jQuery('#aside-id-0').show();
  };

  this.deactivateAllNodes = function()
  {
    this.getNodes().removeClass('active');
    jQuery('.context-browser-doc').hide();
  };

  /*
   * When we are moving a node to a different place we need to:
   *
   * 1) Recreate existing inEdges with the new $target
   * 2) Call draw()
   */
  this.moveNode = function(sourceEl, targetEl)
  {
    var sourceNo = self.digraph.node(sourceEl.data('id'));
    var targetNo = self.digraph.node(targetEl.data('id'));

    // Move element to child logic
    var targetIsSuccessor = -1 !== jQuery.inArray(sourceEl.data('id'), self.digraph.predecessors(targetEl.data('id')));
    if (targetIsSuccessor)
    {
      return false;
    }

    // Detach existing connections
    // TODO: calling detach() individually for a connection didn't work for me
    self.plumb.detachAllConnections(sourceEl);

    // This should happen only once, as only one parent is possible
    self.digraph.inEdges(sourceEl.data('id')).forEach(function(edgeId)
    {
      // Remove existing connection
      var edge = self.digraph.edge(edgeId);
      self.digraph.delEdge(edgeId);

      // Add new edge
      self.addEdgeIntoDigraph(targetEl.data('id'), sourceEl.data('id'), 'hierarchical');
    });

    self.draw();

    // I have no idea why I need to schedule this function but if I call it right
    // away the connections of jsPlumb won't be rendered properly
    window.setTimeout(function()
      {
        self.plumb.repaintEverything();
      }, 0);
  };

  /* ------------------------------------------------------------------------
   * Event callbacks
   * ------------------------------------------------------------------------ */

  this.click = function(event)
  {
    event.preventDefault();
    var target = jQuery(event.target);
    if (target.hasClass('node'))
    {
      this.activateNode(target);
    }
  };

  this.addChildNode = function(event)
  {
    event.preventDefault();

    // Temporary solution to get a random ID for a node
    var makeId = function makeId(length)
    {
      var text = "";
      var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

      for (var i = 0; i < length; i++)
      {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
      }

      return text;
    };

    var activeNodeData = this.getActiveNode();
    if (activeNodeData === undefined)
    {
      return false;
    }

    var n = prompt("Insert name");

    // Add node and edge to digraph
    var newId = makeId(8);
    var newChildNodeId = this.digraph.addNode(newId, {
      id: newId,
      width: self.defaultBoxSize.width,
      height: self.defaultBoxSize.height,
      level: 'description',
      title: n
    });
    this.digraph.addEdge(activeNodeData.id + ':' + newChildNodeId, activeNodeData.id, newChildNodeId, {
      relationType: 'hierarchical'
    });

    // Redraw!
    this.draw();
  };

  this.toggleFullscreen = function(event)
  {
    event.preventDefault();

    var el = document.documentElement;
    var rfs = el.requestFullScreen || el.webkitRequestFullScreen || el.mozRequestFullScreen;

    if (undefined === this.fullscreen | !this.fullscreen)
    {
      rfs.call(el);

      var cb = this.element
        .closest('.context-browser')
        .addClass('context-browser-fullscreen');

      this.element.css(
      {
        width: window.innerWidth,
        height: window.outerHeight
      });

      this.fullscreen = true;

      // TODO redraw
      this.draw();
    }
  };
};
