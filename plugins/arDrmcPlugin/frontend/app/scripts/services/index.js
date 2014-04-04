(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.services', [])

    // Communication with the backend
    .service('AuthenticationService', require('./AuthenticationService'))
    .service('InformationObjectService', require('./InformationObjectService'))
    .service('AIPService', require('./AIPService'))
    .service('ActorsService', require('./ActorsService'))
    .service('SearchService', require('./SearchService'))
    .service('StatisticsService', require('./StatisticsService'))

    // Mixins
    .factory('FullscreenService', require('./FullscreenService'))

    // Global modals
    .service('ModalEditDcMetadataService', require('./ModalEditDcMetadataService'))
    .service('ModalDigitalObjectViewerService', require('./ModalDigitalObjectViewerService'));

})();
