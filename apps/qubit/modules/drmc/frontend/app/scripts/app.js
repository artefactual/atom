'use strict';
// Setup the main module: momaApp
angular.module('momaApp', [
  'momaApp.directives'
])
  .config(function($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/drmc/frontend/app/views/home.html',
        controller: 'HomeCtrl'
      })
      .when('/artworkrecord', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/drmc/frontend/app/views/artworkrecord.html',
        controller: 'ArtworkRecordCtrl'
      })
      .when('/dashboard', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/drmc/frontend/app/views/dashboard.html',
        controller: 'DashboardCtrl'
      })
      .when('/technologyrecord', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/drmc/frontend/app/views/technologyrecord.html',
        controller: 'TechnologyRecordCtrl'
      })
    .otherwise({ redirectTo: '/' });
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

