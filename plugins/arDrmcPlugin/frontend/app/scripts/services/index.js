(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.services', [])

    // Communication with the backend
    .service('AuthenticationService', require('./AuthenticationService'))
    .service('InformationObjectService', require('./InformationObjectService'))
    .service('TaxonomyService', require('./TaxonomyService'))
    .service('AIPService', require('./AIPService'))
    .service('ActorsService', require('./ActorsService'))
    .service('SearchService', require('./SearchService'))
    .service('StatisticsService', require('./StatisticsService'))
    .service('FixityReportService', require('./FixityReportService'))

    // Mixins
    .factory('FullscreenService', require('./FullscreenService'))
    .factory('ParseInputService', require('./ParseInputService'))

    // Global modals
    .service('ModalEditDcMetadataService', require('./ModalEditDcMetadataService'))
    .service('ModalDigitalObjectViewerService', require('./ModalDigitalObjectViewerService'))
    .service('ModalLinkSupportingTechnologyService', require('./ModalLinkSupportingTechnologyService'))
    .service('ModalDownloadService', require('./ModalDownloadService'))
    .service('ModalReclassifyAipService', require('./ModalReclassifyAipService'))
    .service('ModalSaveSearchService', require('./ModalSaveSearchService'));

})();
