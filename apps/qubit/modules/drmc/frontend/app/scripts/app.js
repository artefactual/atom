'use strict';

/*
 * Module definition
 */

angular.module('momaApp.services', []);
angular.module('momaApp.directives', []);
angular.module('momaApp', [

  // Dependencies
  'momaApp.services',
  'momaApp.directives',
  'ngRoute',
  'ui.router',
  '$strap.directives'

])

/*
 * Configuration provider (cfgProvider)
 */

angular.module('momaApp')
  .provider('cfg', function() {
    this.basePath = Qubit.relativeUrlRoot;
    this.frontendPath = Qubit.frontend;
    this.DRMCPath = Qubit.frontend + 'drmc/';
    this.$get = function() {
      var basePath = this.basePath;
      var frontendPath = this.frontendPath;
      var DRMCPath = this.DRMCPath;
    };
  });

// This should be removed in favor of cfgProvider
angular.module('momaApp')
  .factory("atomGlobals", function() {
    return {
      relativeUrlRoot: Qubit.relativeUrlRoot,
      relativeUrlFrontend: Qubit.relativeUrlRoot + '/index.php'
    }
  });

/*
 * Kickstart the application
 *
 * This is executed after all the services have been configured and the injector
 * has been created.
 */
angular.module('momaApp')
  .run(function($rootScope, $state, $stateParams) {

    // Add references to $state and $stateParams to the $rootScope so we can
    // access from them from our entire application
    $rootScope.$state = $state;
    $rootScope.$stateParams = $stateParams;

  });

/*
 * Routing
 */

angular.module('momaApp')
  .config(function($locationProvider, $stateProvider, $urlRouterProvider, cfgProvider) {

    // Use HTML5 mode
    $locationProvider.html5Mode(true).hashPrefix('');

    // Default route
    $urlRouterProvider.otherwise(cfgProvider.DRMCPath + 'dashboard');

    // Define ui-router states
    $stateProvider

      // Dashboard
      .state('dashboard', {
        url: cfgProvider.DRMCPath + 'dashboard',
        controller: 'DashboardCtrl',
        templateUrl: cfgProvider.basePath + '/apps/qubit/modules/drmc/frontend/app/views/dashboard.html'
      })

      // AIPs
      .state('aips', {
        abstract: true,
        url: cfgProvider.DRMCPath + 'aips',
        template: '<ui-view/>'
      })
      .state('aips.browser', {
        url: '',
        controller: 'AIPsBrowserCtrl',
        templateUrl: cfgProvider.basePath + '/apps/qubit/modules/drmc/frontend/app/views/aips.browser.html'
      })
      .state('aips.view', {
        url: '/{aipId}',
        controller: 'AIPsViewCtrl',
        templateUrl: cfgProvider.basePath + '/apps/qubit/modules/drmc/frontend/app/views/aips.view.html'
      })

      // Prototypes and tests
      .state('artwork-record', {
        url: cfgProvider.DRMCPath + 'artwork-record',
        controller: 'ArtworkRecordCtrl',
        templateUrl: cfgProvider.basePath + '/apps/qubit/modules/drmc/frontend/app/views/artwork-record.html'
      })
      .state('artwork-record-2', {
        url: cfgProvider.DRMCPath + 'artwork-record-2',
        controller: 'ArtworkRecord2Ctrl',
        templateUrl: cfgProvider.basePath + '/apps/qubit/modules/drmc/frontend/app/views/artwork-record-2.html'
      })
      .state('technology-record', {
        url: cfgProvider.DRMCPath + 'technology-record',
        controller: 'TechnologyRecordCtrl',
        templateUrl: cfgProvider.basePath + '/apps/qubit/modules/drmc/frontend/app/views/technology-record.html'
      })
      .state('rest-tests', {
        url: cfgProvider.DRMCPath + 'rest-tests',
        controller: 'RestTestsCtrl',
        templateUrl: cfgProvider.basePath + '/apps/qubit/modules/drmc/frontend/app/views/rest-tests.html'
      });

  });
