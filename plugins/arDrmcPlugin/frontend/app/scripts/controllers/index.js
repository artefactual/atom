(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.controllers', [])

    // Dashboard
    .controller('DashboardCtrl', require('./DashboardCtrl'))

    // AIPs
    .controller('AIPBrowserCtrl', require('./AIPBrowserCtrl'))
    .controller('AIPViewCtrl', require('./AIPViewCtrl'))
    .controller('AIPReclassifyCtrl', require('./AIPReclassifyCtrl'))

    // Works
    .controller('WorkBrowserCtrl', require('./WorkBrowserCtrl'))
    .controller('WorkViewCtrl', require('./WorkViewCtrl'))

    // Technology records
    .controller('TechnologyRecordBrowserCtrl', require('./TechnologyRecordBrowserCtrl'))
    .controller('TechnologyRecordViewCtrl', require('./TechnologyRecordViewCtrl'));

})();
