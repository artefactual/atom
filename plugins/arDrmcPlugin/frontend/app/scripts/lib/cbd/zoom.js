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

    this.cbBody = jQuery(this.SVG.node()).closest('.cb-container');

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

  Zoom.prototype.getViewportDimensions = function () {
    return {
      // I could just be looking at .cb-body
      width: this.cbBody.width(),
      height: this.cbBody.height()
    };
  };

  Zoom.prototype.getContainerDimensions = function () {
    return this.container.node().getBBox();
  };

  Zoom.prototype.fitContainer = function () {
    var viewportDimensions = this.getViewportDimensions();
    var vx = viewportDimensions.width;
    var vy = viewportDimensions.height;
    var containerDimensions = this.getContainerDimensions();
    var gx = containerDimensions.width;
    var gy = containerDimensions.height;

    console.log(vx, vy, gx, gy);

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
    var v = this.getViewportDimensions();
    var c = this.container.node().getBoundingClientRect();
    this.translate = [(v.width / 2) - (c.width / 2), (v.height / 2) - (c.height / 2)];
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
