'use strict';

function Plumb(element, configuration)
{
  this.element = element;

  var self = this;

  this.stateMachineConnector = {
    connector: 'StateMachine',
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
  }

  this.initialize = function()
  {
    // Initialization
    console.log('plumb', 'Initializing...');
    this.configure(configuration);
  }

  this.redraw = function(data, transitionDuration)
  {
    console.log('plumb', 'Redrawing...');

    this.plumb.repaintEverything();

    for (var i = 0; i < data.length; i++)
    {
      this.createNode(data[i], true);
    }

    // Make nodes draggable
    this.plumb.draggable(
      this.element.find('.node'),
      { containment: '.plumb-div' });
  }

  this.createNode = function(data, root)
  {
    var node = document.createElement('div');
    node.className = 'node';
    node.id = 'node-' + data.id;

    // Add title
    node.innerHTML = '<span>' + data.title + '</span>';

    // this.locateNode(node, data);

    this.element[0].appendChild(node);

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
  }

  this.locateNode = function(node, data)
  {
    for (var i = 0; i < this.levels.length; i++)
    {
      if (this.levels[i].name == data.level)
      {
        if (this.levels.pile == undefined)
        {
          this.levels.pile = new Array();
        }

        this.levels.pile.push(node);
      }
    }

    jQuery(node)
      .css('left', this.randomLocation(0, this.viewport.width))
      .css('top', this.randomLocation(0, this.viewport.height / 2))
      .css('position', 'relative');
  }

  // Returns a random number between min and max
  this.randomLocation = function(min, max)
  {
    return Math.random() * (max - min) + min;
  }

}

