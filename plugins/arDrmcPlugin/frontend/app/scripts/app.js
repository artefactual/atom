(function () {

  'use strict';

  window.jQuery = require('jquery');
  var angular = require('angular');
  require('../../vendor/angular-ui-router.js');
  require('../../dist/build/vendor-shims.js');

  /*
   * Module definition
   */

  angular.module('momaApp', [

    require('./services').name,
    require('./directives').name,
    require('./controllers').name,
    require('./filters').name,

    'ui.router',
    '$strap'

  ]);


  /*
   * Configuration constants
   */

  angular.module('momaApp')
    .constant('ATOM_CONFIG', {

      // Base path, e.g. "/~user/atom"
      basePath: Qubit.relativeUrlRoot,

      // Frontend path, e.g. "/~user/atom/index.php"
      frontendPath: Qubit.frontend,

      // DRMC path, e.g. "/~user/atom/index.php/drmc"
      DRMCPath: Qubit.frontend + 'drmc/',

      // Views, assets, etc...
      viewsPath: Qubit.relativeUrlRoot + '/plugins/arDrmcPlugin/frontend/app/views',
      assetsPath: Qubit.relativeUrlRoot + '/plugins/arDrmcPlugin/frontend/assets'

    });


  /*
   * Kickstart the application
   *
   * This is executed after all the services have been configured and the injector
   * has been created.
   */

  angular.module('momaApp')
    .run(function ($rootScope, $state, $stateParams, ATOM_CONFIG) {

      // Add references to $state and $stateParams to the $rootScope so we can
      // access from them from our entire application
      $rootScope.$state = $state;
      $rootScope.$stateParams = $stateParams;

      // Same here
      $rootScope.viewsPath = ATOM_CONFIG.viewsPath;
      $rootScope.assetsPath = ATOM_CONFIG.assetsPath;

    });


  /*
   * Routing
   */

  angular.module('momaApp')
    .config(function ($locationProvider, $stateProvider, $urlRouterProvider, ATOM_CONFIG) {

      // Use HTML5 mode
      $locationProvider.html5Mode(true);
      $locationProvider.hashPrefix('!');

      // Default route
      $urlRouterProvider.otherwise(ATOM_CONFIG.DRMCPath + 'dashboard');

      // Define ui-router states
      $stateProvider

        // Dashboard
        .state('dashboard', {
          url: ATOM_CONFIG.DRMCPath + 'dashboard',
          controller: 'DashboardCtrl',
          templateUrl: ATOM_CONFIG.viewsPath + '/dashboard.html'
        })

        // AIPs
        .state('aips', {
          abstract: true,
          url: ATOM_CONFIG.DRMCPath + 'aips',
          template: '<ui-view/>'
        })
        .state('aips.browser', {
          url: '',
          controller: 'AIPBrowserCtrl',
          templateUrl: ATOM_CONFIG.viewsPath + '/aips.browser.html'
        })
        .state('aips.view', {
          url: '/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}',
          controller: 'AIPViewCtrl',
          templateUrl: ATOM_CONFIG.viewsPath + '/aips.view.html'
        })

        // Works
        .state('works', {
          abstract: true,
          url: ATOM_CONFIG.DRMCPath + 'works',
          template: '<ui-view/>'
        })
        .state('works.browser', {
          url: '',
          controller: 'WorkBrowserCtrl',
          templateUrl: ATOM_CONFIG.viewsPath + '/works.browser.html'
        })
        .state('works.view', {
          url: '/{id}',
          controller: 'WorkViewCtrl',
          templateUrl: ATOM_CONFIG.viewsPath + '/works.view.html'
        })

        // Prototypes and tests
        .state('artwork-record', {
          url: ATOM_CONFIG.DRMCPath + 'artwork-record',
          controller: 'ArtworkRecordCtrl',
          templateUrl: ATOM_CONFIG.viewsPath + '/artwork-record.html'
        })
        .state('artwork-record-2', {
          url: ATOM_CONFIG.DRMCPath + 'artwork-record-2',
          controller: 'ArtworkRecord2Ctrl',
          templateUrl: ATOM_CONFIG.viewsPath + '/artwork-record-2.html'
        })
        .state('technology-record', {
          url: ATOM_CONFIG.DRMCPath + 'technology-record',
          controller: 'TechnologyRecordCtrl',
          templateUrl: ATOM_CONFIG.viewsPath + '/technology-record.html'
        });

    });

})();
