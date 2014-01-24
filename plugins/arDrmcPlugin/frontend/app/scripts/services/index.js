(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.services', [])
    .service('DashboardService', require('./DashboardService'))
    .service('AIPService', require('./AIPService'));

})();
