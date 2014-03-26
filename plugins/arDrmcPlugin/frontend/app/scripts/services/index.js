(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.services', [])

    // Communication with the backend
    .service('DashboardService', require('./DashboardService'))
    .service('InformationObjectService', require('./InformationObjectService'))
    .service('AIPService', require('./AIPService'))
    .service('ActorsService', require('./ActorsService'))

    // Mixins
    .factory('FullscreenService', require('./FullscreenService'))

    // Global modals
    .service('ModalEditDcMetadataService', require('./ModalEditDcMetadataService'));

})();
