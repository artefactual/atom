'use strict';

module.exports = function ($locationProvider, $stateProvider, $urlRouterProvider, SETTINGS) {

  // Use HTML5 mode
  $locationProvider.html5Mode(true);
  $locationProvider.hashPrefix('!');

  // Default route
  $urlRouterProvider.otherwise(SETTINGS.DRMCPath + '404');

  // Define ui-router states
  $stateProvider

    // Login page
    .state('login', {
      url: SETTINGS.DRMCPath + 'login',
      controller: 'LoginCtrl',
      templateUrl: SETTINGS.viewsPath + '/login.html'
    })

    // 404 page
    .state('404', {
      url: SETTINGS.DRMCPath + '404',
      templateUrl: SETTINGS.viewsPath + '/404.html'
    })

    // Includes header and footer, used in most of the pages.
    .state('main', {
      abstract: true,
      templateUrl: SETTINGS.viewsPath + '/layout/main.html'
    })

    // Dashboard
    .state('main.dashboard', {
      url: SETTINGS.DRMCPath.replace(/\/$/, ''),
      controller: 'DashboardCtrl',
      templateUrl: SETTINGS.viewsPath + '/dashboard.html'
    })

    // AIPs
    .state('main.aips', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'aips',
      template: '<ui-view autoscroll="false"/>'
    })
    .state('main.aips.view', {
      url: '/{uuid:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}',
      controller: 'AIPViewCtrl',
      templateUrl: SETTINGS.viewsPath + '/aips.view.html'
    })

    // Works
    .state('main.works', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'works',
      template: '<ui-view autoscroll="false"/>'
    })
    .state('main.works.view', {
      url: '/{id}',
      controller: 'WorkViewCtrl',
      templateUrl: SETTINGS.viewsPath + '/works.view.html'
    })

    // Technology Records
    .state('main.technology-records', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'technology-records',
      template: '<ui-view autoscroll="false"/>'
    })
    .state('main.technology-records.view', {
      url: '/{id}',
      controller: 'TechnologyRecordViewCtrl',
      templateUrl: SETTINGS.viewsPath + '/technology-records.view.html'
    })

    // TMS
    .state('main.tms', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'tms',
      template: '<ui-view autoscroll="false"/>'
    })
    .state('main.tms.browser', {
      url: '',
      controller: 'TmsBrowserCtrl',
      templateUrl: SETTINGS.viewsPath + '/tms.browser.html'
    })

    // Search
    .state('main.search', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'search',
      template: '<ui-view autoscroll="false"/>'
    })
    .state('main.search.entity', {
      url: '/{entity}',
      controller: 'SearchCtrl',
      templateUrl: function (stateParams) {
        return SETTINGS.viewsPath + '/' + stateParams.entity + '.search.html';
      }
    });

};
