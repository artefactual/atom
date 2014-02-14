(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.filters', [])
    .filter('UnitFilter', require('./UnitFilter'));

})();
