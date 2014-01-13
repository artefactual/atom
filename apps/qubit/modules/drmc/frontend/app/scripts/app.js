'use strict';

// Setup the main module: momaApp
angular.module('momaApp', [
  'momaApp.directives',
  'ngRoute',
  'ui.router',
  '$strap.directives'
])
  .config(function($routeProvider) {
    $routeProvider
      .when('/dashboard', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/drmc/frontend/app/views/dashboard.html',
        controller: 'DashboardCtrl',
        activeTab: 'dashboard'
      })
      .when('/artwork-record', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/drmc/frontend/app/views/artwork-record.html',
        controller: 'ArtworkRecordCtrl',
        activeTab: 'artwork-record'
      })
      .when('/artwork-record2', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/drmc/frontend/app/views/artwork-record2.html',
        controller: 'ArtworkRecord2Ctrl',
        activeTab: 'artwork-record2'
      })
      .when('/technology-record', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/drmc/frontend/app/views/technology-record.html',
        controller: 'TechnologyRecordCtrl',
        activeTab: 'technology-record'
      })
      .when('/rest-tests', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/drmc/frontend/app/views/rest-tests.html',
        controller: 'RestTestsCtrl',
        activeTab: 'rest-tests'
      })
    .otherwise({ redirectTo: '/dashboard' });
  })

  .config(function ($locationProvider) {
    $locationProvider.html5Mode(false);
  })

  .factory("atomGlobals", function() {
    return {
      relativeUrlRoot: Qubit.relativeUrlRoot,
      relativeUrlFrontend: Qubit.relativeUrlRoot + '/index.php'
    }
  });

// Setup dependency injection
angular.module('momaApp.directives', []);
