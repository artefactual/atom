(function () {

  'use strict';

  require('./node');

  module.exports = function () {

    this.nodeList = [];
    this.nodes = {};

    this.debug = function () {
      console.log('debug');
    };

  };

})();
