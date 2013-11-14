'use strict';

function Plumb(element, configuration)
{
  this.element = element;

  var self = this;

  this.stateMachineConnector = {
    connector: 'Straight',
      paintStyle: {
        lineWidth: 1,
        strokeStyle: '#cecece'
      },
    endpoint: 'Blank',
    anchor: 'Continuous',
  };

  this.levels = [
    { name: 'Work' },
    { name: 'Expression' },
    { name: 'Manifestation' },
    { name: 'Component' }
  ];

  this.configure = function(configuration)
  {
    console.log('plumb', 'Configuring...');

    this.config = configuration;

    // Create an instance of jsPlumb
    this.plumb = jsPlumb.getInstance();

    // Change jsPlumb.Defaults
    this.plumb.importDefaults({
      Container: element
    });

    // Create an object with information about the viewport
    this.viewport = {
      width: this.element.innerWidth(),
      height: this.element.innerHeight(),
      columns: this.levels.length
    };

    // Attach a callback to the click event for .node elements. Should we do
    // this from the angular directive?
    this.element.on('click', '.node',
      {
        // Pass jsPlumb as we are going to need it
        plumb: element
      },
      // Callback. It is executed each time a node is clicked in jsPlumb.
      function(event) {

        // Create a jQuery object from the DOM element
        var node = jQuery(this);

        // Each node has its own Id which is stored in the DOM element as a data
        // attribute. Let's extract it.
        var id = node.data('id');

        // This is going to be the aside that correspond to the node
        var aside = jQuery('#aside-id-' + id);

        // It's time to hide any aside before we proceed
        jQuery('.context-browser-doc, .context-browser-default').hide();

        // In case that the aside could not be found, show the default aside
        if (!aside.length)
        {
          jQuery('.context-browser-default').show();
        }
        // Otherwise, show the aside that correspond to the node
        else
        {
          aside.show();
        }
      });
  };

  this.initialize = function()
  {
    // Initialization
    console.log('plumb', 'Initializing...');
    this.configure(configuration);
  };

  this.redraw = function(data, transitionDuration)
  {
    console.log('plumb', 'Redrawing...');

    this.createNodes(data);

    this.plumb.repaintEverything();

    // Make nodes draggable - Heather took this off by removing .draggable( after this.plub.dragg...
    this.plumb(
      this.element.find('.node'),
      { containment: '.plumb-div' });
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
    var node = document.createElement('div');
    node.className = 'node';
    node.id = 'node-' + data.id;
    node.setAttribute('data-id', data.id);

    // Add title
    node.innerHTML = '<span>' + data.title + '</span>';

    // Append the node to jsPlumb
    this.element[0].appendChild(node);

    // Iterate children (recursion)
    if (angular.isArray(data.children))
    {
      for (var i = 0; i < data.children.length; i++)
      {
        var child = this.createNode(data.children[i]);

        this.plumb.connect({
          source: node,
          target: child,
          dragOptions: {
            cursor: 'crosshair'
          },
        }, this.stateMachineConnector);
      }
    }

    return node;
  };
}
