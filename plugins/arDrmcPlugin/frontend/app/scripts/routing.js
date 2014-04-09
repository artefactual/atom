'use strict';

module.exports = function ($locationProvider, $stateProvider, $urlRouterProvider, SETTINGS) {

  // Use HTML5 mode
  $locationProvider.html5Mode(true);
  $locationProvider.hashPrefix('!');

  // Default route
  $urlRouterProvider.otherwise(SETTINGS.DRMCPath + '404');

  function redirectIfNotAuthorized($state, AuthenticationService) {
    if (!AuthenticationService.isAuthenticated()) {
      $state.go('login');
    }
  }

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
      templateUrl: SETTINGS.viewsPath + '/dashboard.html',
      onEnter: redirectIfNotAuthorized
    })

    // AIPs
    .state('aips', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'aips',
      template: '<ui-view/>',
      onEnter: redirectIfNotAuthorized
    })
    .state('aips.browser', {
      url: '',
      controller: 'AIPBrowserCtrl',
      templateUrl: SETTINGS.viewsPath + '/aips.browser.html',
      onEnter: redirectIfNotAuthorized
    })
    .state('aips.view', {
      url: '/{uuid:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}',
      controller: 'AIPViewCtrl',
      templateUrl: SETTINGS.viewsPath + '/aips.view.html',
      onEnter: redirectIfNotAuthorized
    })

    // Works
    .state('works', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'works',
      template: '<ui-view/>',
      onEnter: redirectIfNotAuthorized
    })
    .state('works.browser', {
      url: '',
      controller: 'WorkBrowserCtrl',
      templateUrl: SETTINGS.viewsPath + '/works.browser.html',
      onEnter: redirectIfNotAuthorized
    })
    .state('works.view', {
      url: '/{id}',
      controller: 'WorkViewCtrl',
      templateUrl: SETTINGS.viewsPath + '/works.view.html',
      onEnter: redirectIfNotAuthorized
    })

    // Technology Records
    .state('technology-records', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'technology-records',
      template: '<ui-view/>',
      onEnter: redirectIfNotAuthorized
    })
    .state('technology-records.browser', {
      url: '',
      controller: 'TechnologyRecordBrowserCtrl',
      templateUrl: SETTINGS.viewsPath + '/technology-records.browser.html',
      onEnter: redirectIfNotAuthorized

    })
    .state('technology-records.view', {
      url: '/{id}',
      controller: 'TechnologyRecordViewCtrl',
      templateUrl: SETTINGS.viewsPath + '/technology-records.view.html',
      onEnter: redirectIfNotAuthorized
    })

    // TMS
    .state('tms', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'tms',
      template: '<ui-view/>',
      onEnter: redirectIfNotAuthorized
    })
    .state('tms.browser', {
      url: '',
      controller: 'TmsBrowserCtrl',
      templateUrl: SETTINGS.viewsPath + '/tms.browser.html',
      onEnter: redirectIfNotAuthorized
    })

    // Search
    .state('search', {
      abstract: true,
      url: SETTINGS.DRMCPath + 'search',
      template: '<ui-view/>',
      onEnter: redirectIfNotAuthorized
    })
    .state('search.entity', {
      url: '/{entity}',
      controller: 'SearchCtrl',
      templateUrl: function (stateParams) {
        return SETTINGS.viewsPath + '/' + stateParams.entity + '.search.html';
      },
      onEnter: redirectIfNotAuthorized
    });

};
