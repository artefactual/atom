'use strict';

// Setup the main module: momaApp
angular.module('momaApp', [
  'momaApp.directives'
])
  .config(function($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/moma/frontend/app/views/main.html',
        controller: 'MainCtrl'
    })
      .when('/about', {
        templateUrl: Qubit.relativeUrlRoot + '/apps/qubit/modules/moma/frontend/app/views/about.html',
        controller: 'AboutCtrl'
    })
    .otherwise({ redirectTo: '/' });
  })
  .config(function ($locationProvider) {
    $locationProvider.html5Mode(false);
  });

// Setup dependency injection
angular.module('jsPlumb', []);
angular.module('momaApp.directives', ['jsPlumb']);
