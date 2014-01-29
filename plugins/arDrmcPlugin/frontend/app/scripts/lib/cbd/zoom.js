(function () {

  'use strict';

  var d3 = require('d3');

  function Zoom (SVG) {

    var self = this;

    this.SVG = SVG;

    this.zoomBehavior = d3.behavior.zoom().on('zoom', function () {
      var ev = d3.event;
      self.SVG.select('g').attr('transform', 'translate(' + ev.translate + ') scale(' + ev.scale + ')');
    });

    this.SVG.call(this.zoomBehavior);

  }

  module.exports = Zoom;

})();
