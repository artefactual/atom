(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.filters', [])
    .filter('EmptyFilter', require('./EmptyFilter'))
    .filter('ConvertSecondsFilter', require('./ConvertSecondsFilter'))
    .filter('UnitFilter', require('./UnitFilter'));

})();
