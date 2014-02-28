'use strict';

module.exports = function ($locationProvider, $stateProvider, $urlRouterProvider, ATOM_CONFIG) {

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
      url: '/{uuid:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}',
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

    // Technology Records
    .state('technology-records', {
      abstract: true,
      url: ATOM_CONFIG.DRMCPath + 'technology-records',
      template: '<ui-view/>'
    })
    .state('technology-records.browser', {
      url: '',
      controller: 'TechnologyRecordBrowserCtrl',
      templateUrl: ATOM_CONFIG.viewsPath + '/technology-records.browser.html'
    })
    .state('technology-records.view', {
      url: '/{id}',
      controller: 'TechnologyRecordViewCtrl',
      templateUrl: ATOM_CONFIG.viewsPath + '/technology-records.view.html'
    });

};
