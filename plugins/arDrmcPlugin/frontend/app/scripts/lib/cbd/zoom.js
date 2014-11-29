(function () {

  'use strict';

  // Click to zoom via transform,
  // I feel like I'm going to need this soon
  // http://bl.ocks.org/mbostock/2206590

  function Zoom (SVG) {
    this.SVG = SVG;
    this.container = this.SVG.select('svg > g');

    this.cbBody = jQuery(this.SVG.node()).closest('.cb-container');

    this.scale = 1;

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

  window.GraphZoom = Zoom;

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

    // We are not transforming the object if it fits within the viewport, but
    // reset its scale in case that the user changed it
    if (gx < vx && gy < vy) {
      this.scale = 1;
      return;
    }

    var margin = 40;
    this.scale = Math.min(vx / (gx + margin), vy / (gy + margin));

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

})();
