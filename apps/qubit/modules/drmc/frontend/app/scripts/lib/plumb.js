'use strict';

function Plumb(element, configuration)
{
  this.element = element;

  var self = this;

  this.levels = [
    { name: 'Work' },
    { name: 'Expression' },
    { name: 'Manifestation' },
    { name: 'Component' }
  ];

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
        connector: [ 'Bezier', { curviness: 50 }],
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

  this.initialize = function()
  {
    // Initialization
    console.log('plumb', 'Initializing...');
    this.configure(configuration);
    this.listen();
  };

  this.configure = function(configuration)
  {
    console.log('plumb', 'Configuring...');

    this.config = configuration;

    // Create an instance of jsPlumb
    this.plumb = jsPlumb.getInstance();

    // Change jsPlumb.Defaults
    this.plumb.importDefaults(this.jsPlumbConfiguration.defaults);

    // Create a new directed graph
    this.dagreDigraph = new dagre.Digraph();
  };

  this.listen = function()
  {
    this.element
      .on('click', jQuery.proxy(this.click, this));

    jQuery(this.element).closest('.plumb-div').prev()
      .on('click', '.fullscreen', jQuery.proxy(this.toggleFullscreen, this));
  };

  this.redraw = function(data)
  {
    console.log('plumb', 'Redrawing...');

    this.createNodes(data.collection);

    this.createRelations(data.relations);

    var layout = dagre.layout()
                  .nodeSep(-40)
                  .rankSep(140)
                  .rankDir("TB")
                  .run(this.dagreDigraph);

    layout.eachNode(function(u, value) {
      var node = document.getElementById('node-' + u);
      node.style.position = "absolute";
      node.style.top = value.x + "px";
      node.style.left = value.y + "px";
    });

    this.plumb.repaintEverything();

    this.activateDefaultNode();
  };

  this.createNodes = function(data)
  {
    for (var i = 0; i < data.length; i++)
    {
      this.createNode(data[i], true);
    }
  };

  this.createNode = function(data, root)
  {
    // Create the DOM element
    var node = document.createElement('span');
    node.className = 'node node-level-' + data.level;
    node.id = 'node-' + data.id;
    node.setAttribute('data-id', data.id);

    // Add title
    node.innerHTML = data.title;
    // node.innerHTML = '<span>' + data.title + '</span>';

    // Append the node to jsPlumb
    this.element[0].appendChild(node);

    this.dagreDigraph.addNode(data.id, { width: 120, height: 32 });

    // Iterate children (recursion)
    if (angular.isArray(data.children))
    {
      for (var i = 0; i < data.children.length; i++)
      {
        var child = this.createNode(data.children[i]);

        this.plumb.connect({
          source: node,
          target: child,
        }, this.jsPlumbConfiguration.connectors.parentHood);

        this.dagreDigraph.addEdge(null, node.getAttribute('data-id'), child.getAttribute('data-id'));
      }
    }

    return node;
  };

  this.createRelations = function(relations)
  {
    for (var i = 0; i < relations.length; i++)
    {
      var relation = relations[i];

      this.plumb.connect({
        source: document.getElementById('node-' + relation.source),
        target: document.getElementById('node-' + relation.target),
      }, this.jsPlumbConfiguration.connectors.derivativeOf);

      // this.dagreDigraph.addEdge(null, relation.source, relation.target);
    }
  };

  this.getNodes = function(relations)
  {
    return jQuery(this.element).find('.node');
  };

  this.click = function(event)
  {
    event.preventDefault();
    var target = jQuery(event.target);
    if (target.hasClass('node'))
    {
      this.activateNode(target);
    }
  }

  this.activateNode = function(node)
  {
    var id = node.data('id');
    var aside = jQuery('#aside-id-' + id);

    if (node.hasClass('active') || !aside.length)
    {
      return;
    }

    this.deactivateAllNodes();
    node.addClass('active');
    aside.show();
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

  this.toggleFullscreen = function(event)
  {
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
