(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.controllers', [])

    // Layout
    .controller('HeaderCtrl', require('./HeaderCtrl'))
    .controller('FooterCtrl', require('./FooterCtrl'))

    // Dashboard
    .controller('DashboardCtrl', require('./DashboardCtrl'))

    // AIPs
    .controller('AIPBrowserCtrl', require('./AIPBrowserCtrl'))
    .controller('AIPViewCtrl', require('./AIPViewCtrl'))
    .controller('AIPReclassifyCtrl', require('./AIPReclassifyCtrl'))

    // Works
    .controller('WorkBrowserCtrl', require('./WorkBrowserCtrl'))
    .controller('WorkViewCtrl', require('./WorkViewCtrl'))
    .controller('DigitalObjectViewerCtrl', require('./DigitalObjectViewerCtrl'))

    // Technology records
    .controller('TechnologyRecordBrowserCtrl', require('./TechnologyRecordBrowserCtrl'))
    .controller('TechnologyRecordViewCtrl', require('./TechnologyRecordViewCtrl'))
    .controller('EditDCMetadataCtrl', require('./EditDCMetadataCtrl'))
    .controller('AddSupportingTechnologyCtrl', require('./AddSupportingTechnologyCtrl'))

    // TMS
    .controller('TmsBrowserCtrl', require('./TmsBrowserCtrl'))

    // Search
    .controller('SearchCtrl', require('./SearchCtrl'));

})();
