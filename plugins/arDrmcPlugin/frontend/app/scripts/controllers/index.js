(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.controllers', [])

    // Layout
    .controller('BodyCtrl', require('./BodyCtrl'))
    .controller('HeaderCtrl', require('./HeaderCtrl'))
    .controller('FooterCtrl', require('./FooterCtrl'))

    // Login
    .controller('LoginCtrl', require('./LoginCtrl'))

    // Modals
    .controller('EditDcMetadataCtrl', require('./EditDcMetadataCtrl'))
    .controller('DigitalObjectViewerCtrl', require('./DigitalObjectViewerCtrl'))
    .controller('AIPReclassifyCtrl', require('./AIPReclassifyCtrl'))
    .controller('LinkSupportingTechnologyCtrl', require('./LinkSupportingTechnologyCtrl'))

    // Dashboard
    .controller('DashboardCtrl', require('./DashboardCtrl'))
    .controller('DashboardRecentActivityCtrl', require('./DashboardRecentActivityCtrl'))
    .controller('DashboardIngestionCtrl', require('./DashboardIngestionCtrl'))

    // Search
    .controller('SearchCtrl', require('./SearchCtrl'))

    // Context browser
    .controller('ContextBrowserCtrl', require('./ContextBrowserCtrl'))

    // AIPs, works, technology records...
    .controller('AIPBrowserCtrl', require('./AIPBrowserCtrl'))
    .controller('AIPViewCtrl', require('./AIPViewCtrl'))
    .controller('WorkViewCtrl', require('./WorkViewCtrl'))
    .controller('TechnologyRecordViewCtrl', require('./TechnologyRecordViewCtrl'))

    // TMS lookup
    .controller('TmsBrowserCtrl', require('./TmsBrowserCtrl'));

})();
