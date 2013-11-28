'use strict';

// Setup the main module: momaApp
angular.module('momaApp', [
  'momaApp.directives',
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
      .when('/technology-record', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/drmc/frontend/app/views/technology-record.html',
        controller: 'TechnologyRecordCtrl',
        activeTab: 'technology-record'
      })
    .otherwise({ redirectTo: '/dashboard' });
  })

  .config(function ($locationProvider) {
    $locationProvider.html5Mode(false);
  })

  .factory("atomGlobals", function() {
    return {
      relativeUrlRoot: Qubit.relativeUrlRoot
    }
  });

// Setup dependency injection
angular.module('jsPlumb', []);
angular.module('momaApp.directives', ['jsPlumb']);
