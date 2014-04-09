(function () {

  'use strict';

  var angular = require('angular');

  require('../../vendor/angular-ui.js');
  require('../../vendor/cookies.js');

  /*
   * Define module and dependencies
   */

  angular.module('momaApp', [

    require('./services').name,
    require('./directives').name,
    require('./controllers').name,
    require('./filters').name,

    'ui.router',
    'ui.bootstrap',
    'ngCookies'

  ]);


  /*
   * Routing
   */

  angular.module('momaApp').config(require('./routing'));


  /*
   * Settings
   */

  angular.module('momaApp').constant('SETTINGS', require('./settings'));


  /*
   * Kickstart the application
   *
   * This is executed after all the services have been configured and the injector
   * has been created.
   */

  angular.module('momaApp')
    .run(function ($rootScope, SETTINGS, $state, $stateParams, AuthenticationService) {

      // Share information with $rootScope so it's globally available from our views
      $rootScope.$state = $state;
      $rootScope.$stateParams = $stateParams;
      $rootScope.basePath = SETTINGS.basePath;
      $rootScope.viewsPath = SETTINGS.viewsPath;
      $rootScope.assetsPath = SETTINGS.assetsPath;

      // Redirect users to the login page
      var allowedNames = ['login', '404'];
      $rootScope.$on('$stateChangeStart', function (event, toState) {
        if (allowedNames.indexOf(toState.name) === -1 && !AuthenticationService.isAuthenticated()) {
          event.preventDefault();
          $state.go('login');
        }
      });

    });

})();
