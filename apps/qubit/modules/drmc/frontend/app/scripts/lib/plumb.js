'use strict';

function Plumb(element, scope)
{
  var self = this;

  this.element = element;
  this.scope = scope;

  this.levels = [
    { name: 'Work' },
    { name: 'Expression' },
    { name: 'Manifestation' },
    { name: 'Component' }
  ];

  this.defaultBoxSize = {
    width: 120,
    height: 24
  };

  this.jsPlumbConfiguration = {
    defaults: {
      Container: this.element
    },
    connectors: {
      parentHood: {
        connector: 'Straight',
        anchors: ['Right', 'Left'],
        paintStyle: {
          lineWidth: 1,
          strokeStyle: '#cecece'
        },
        endpoint: 'Blank',
      },
      derivativeOf: {
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
        label: 'is derivative of',
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

    jQuery(this.element).closest('.plumb-div').prev()
      .on('click', '.fullscreen', jQuery.proxy(this.toggleFullscreen, this))
      .on('click', '.add_child', jQuery.proxy(this.addChildNode, this));
  };

  this.draw = function()
  {
    console.log('plumb', 'Drawing context browser');

    // Use dagre to build the layout by passing the digraph
    var layout = dagre.layout().nodeSep(-70).rankSep(140).rankDir("TB").run(this.digraph);

    this.element.children().remove();

    // Update size of the container
    this.element.css({
      'height': layout.graph().height + 140
    });

    layout.eachNode(function(id, dagreLayout) {
      var node = self.digraph.node(id);
      node.domEl = self.renderNode(id, node, dagreLayout);
    });

    layout.eachEdge(function(edgeId, sourceId, targetId, dagreLayout) {
      var source = self.digraph.node(sourceId);
      var target = self.digraph.node(targetId);
      var edge = self.digraph.edge(edgeId);
      edge.jsPlumbConnection = self.renderEdge(edgeId, source.domEl, target.domEl, edge.relationType, dagreLayout);
    });

    this.plumb.repaintEverything();

    if (this.firstRender === undefined)
    {
      this.activateDefaultNode();
      this.firstRender = false;
    }
  };

  /*
   * @return {DOMElement}
   */
  this.renderNode = function(id, data, dagreLayout)
  {
    var el = document.createElement('span');
    el.className = 'node node-level-' + data.level;
    el.id = 'node-' + id;
    el.setAttribute('data-id', id);
    el.innerHTML = data.title;
    el.style.position = "absolute";
    el.style.top = dagreLayout.x + 'px';
    el.style.left = dagreLayout.y + 'px';
    el.style.width = this.defaultBoxSize.width + 'px';
    el.style.height = this.defaultBoxSize.height + 'px';
    el.style.lineHeight = this.defaultBoxSize.height + 'px';
    self.element[0].appendChild(el);

    return el;
  };

  /*
   * @return {jsPlumb.Connection}
   */
  this.renderEdge = function(id, sourceDomEl, targetDomEl, relationType, dagreLayout)
  {
    return self.plumb.connect({
      source: sourceDomEl,
      target: targetDomEl,
    }, self.jsPlumbConfiguration.connectors[relationType]);
  };

  this.loadDataIntoDigraph = function()
  {
    var addNode = function(node, isRoot)
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
          var child = addNode(node.children[i], false);
          addRelation(node.id, child.id, 'parentHood');
        }
      }

      return node;
    };

    var addRelation = function(sourceId, targetId, relationType)
    {
      self.digraph.addEdge(sourceId + ':' + targetId, sourceId, targetId, {
        relationType: relationType
      });
    };

    // Load collection
    for (var i = 0; i < this.scope.collection.length; i++)
    {
      addNode(this.scope.collection[i], true);
    }

    // Load relations
    if (this.scope.relations !== undefined)
    {
      for (var i = 0; i < this.scope.relations.length; i++)
      {
        addRelation(
          this.scope.relations[i].source,
          this.scope.relations[i].target,
          'derivativeOf');
      }
    }
  };

  this.getNodes = function(relations)
  {
    return jQuery(this.element).find('.node');
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

  this.click = function(event)
  {
    event.preventDefault();
    var target = jQuery(event.target);
    if (target.hasClass('node'))
    {
      this.activateNode(target);
    }
  };

  this.activateNode = function(node)
  {
    var id = node.data('id');
    var aside = jQuery('#aside-id-' + id);

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

  this.addChildNode = function(event)
  {
    event.preventDefault();

    var makeId = function makeId(length)
    {
      var text = "";
      var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

      for (var i = 0; i < length; i++)
      {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
      }

      return text;
    }

    var activeNodeData = this.getActiveNode();

    var n = prompt("Insert name");

    // Add node and edge to digraph
    var newId = makeId(8);
    var newChildNodeId = this.digraph.addNode(newId, {
      id: newId,
      width: self.defaultBoxSize.width,
      height: self.defaultBoxSize.height,
      level: 'Expression',
      title: n
    });
    this.digraph.addEdge(activeNodeData.id + ':' + newChildNodeId, activeNodeData.id, newChildNodeId, {
      relationType: 'parentHood'
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
    }
  };

  }

