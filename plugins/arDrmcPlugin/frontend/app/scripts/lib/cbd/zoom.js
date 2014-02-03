(function () {

  'use strict';

  var d3 = require('d3');
  var jQuery = require('jquery');

  // Click to zoom via transform,
  // I feel like I'm going to need this soon
  // http://bl.ocks.org/mbostock/2206590

  function Zoom (SVG) {
    this.SVG = SVG;
    this.container = this.SVG.select('svg > g');

    // Do I really need to use jQuery? Try maybe getBoundingClientRect()?
    // TODO: height is a dirty hack that needs to be fixed
    var $SVG = jQuery(this.SVG.node());

    // We are going to need this later
    this.viewportBox = { width: this.SVG.node().getBoundingClientRect().width, height: $SVG.closest('.cb').height() - 40 };
    this.containerBox = this.container.node().getBBox();

    this.scale = 1;
    this.translate = [0, 0];

    this.fitContainer();
    this.centerContainer();

    // Setup zoom behavior
    this.zoomBehavior = d3.behavior.zoom()
      .scale(this.scale)
      .translate(this.translate)
      .on('zoom', jQuery.proxy(this.zoomed, this));
    this.SVG.call(this.zoomBehavior);
    this.SVG.on('dblclick.zoom', null);
  }

  Zoom.prototype.fitContainer = function () {
    var vx = this.viewportBox.width;
    var vy = this.viewportBox.height;
    var gx = this.containerBox.width;
    var gy = this.containerBox.height;

    // We are not transforming the object if it fits within the viewport
    if (gx < vx && gy < vy) {
      return;
    }

    // There must be a cleaner way to do this! :(
    var margin = 40;
    var longest = Math.max(gx, gy);
    var related = longest === gx ? vx : vy;
    this.scale = 1 / (longest / (related - margin));

    this.container.attr('transform', 'translate(0, 0) scale(' + this.scale + ')');
  };

  Zoom.prototype.centerContainer = function () {
    var c = this.container.node().getBoundingClientRect();
    this.translate = [(this.viewportBox.width / 2) - (c.width / 2), (this.viewportBox.height / 2) - (c.height / 2)];
    this.container.attr('transform', 'translate(' + this.translate + ') scale(' + this.scale + ')');
  };

  Zoom.prototype.reset = function () {
    this.fitContainer();
    this.centerContainer();
    this.zoomBehavior
      .scale(this.scale)
      .translate(this.translate);
  };

  Zoom.prototype.zoomed = function () {
    this.container.attr('transform', 'translate(' + d3.event.translate + ') scale(' + d3.event.scale + ')');
  };

  module.exports = Zoom;

})();
