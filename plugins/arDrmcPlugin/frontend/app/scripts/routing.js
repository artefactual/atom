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

    // Dashboard
    .state('dashboard', {
      url: SETTINGS.DRMCPath.replace(/\/$/, ''),
      controller: 'DashboardCtrl',
      templateUrl: SETTINGS.viewsPath + '/dashboard.html'
    })

    // AIPs
    .state('aips', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'aips',
      template: '<ui-view/>'
    })
    .state('aips.browser', {
      url: '',
      controller: 'AIPBrowserCtrl',
      templateUrl: SETTINGS.viewsPath + '/aips.browser.html'
    })
    .state('aips.view', {
      url: '/{uuid:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}',
      controller: 'AIPViewCtrl',
      templateUrl: SETTINGS.viewsPath + '/aips.view.html'
    })

    // Works
    .state('works', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'works',
      template: '<ui-view/>'
    })
    .state('works.browser', {
      url: '',
      controller: 'WorkBrowserCtrl',
      templateUrl: SETTINGS.viewsPath + '/works.browser.html'
    })
    .state('works.view', {
      url: '/{id}',
      controller: 'WorkViewCtrl',
      templateUrl: SETTINGS.viewsPath + '/works.view.html'
    })

    // Technology Records
    .state('technology-records', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'technology-records',
      template: '<ui-view/>'
    })
    .state('technology-records.browser', {
      url: '',
      controller: 'TechnologyRecordBrowserCtrl',
      templateUrl: SETTINGS.viewsPath + '/technology-records.browser.html'
    })
    .state('technology-records.view', {
      url: '/{id}',
      controller: 'TechnologyRecordViewCtrl',
      templateUrl: SETTINGS.viewsPath + '/technology-records.view.html'
    })

    // TMS
    .state('tms', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'tms',
      template: '<ui-view/>'
    })
    .state('tms.browser', {
      url: '',
      controller: 'TmsBrowserCtrl',
      templateUrl: SETTINGS.viewsPath + '/tms.browser.html'
    })

    // Search
    .state('search', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'search',
      template: '<ui-view/>'
    })
    .state('search.entity', {
      url: '/{entity}',
      controller: 'SearchCtrl',
      templateUrl: function (stateParams) {
        return SETTINGS.viewsPath + '/' + stateParams.entity + '.search.html';
      }
    });

};
