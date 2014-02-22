(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.filters', [])
    .filter('EmptyFilter', require('./EmptyFilter'))
    .filter('UnitFilter', require('./UnitFilter'));

})();
