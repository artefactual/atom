(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.services', [])

    .service('DashboardService', require('./DashboardService'))
    .service('InformationObjectService', require('./InformationObjectService'))
    .service('AIPService', require('./AIPService'))

    .factory('FullscreenService', require('./FullscreenService'));

})();
