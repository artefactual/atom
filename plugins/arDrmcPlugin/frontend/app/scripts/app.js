(function () {

  'use strict';

  var angular = require('angular');

  require('../../vendor/angular-ui.js');

  /*
   * Define module and dependencies
   */

  angular.module('momaApp', [

    require('./services').name,
    require('./directives').name,
    require('./controllers').name,
    require('./filters').name,

    'ui.router',
    'ui.bootstrap'

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

      // Add references to $state and $stateParams to the $rootScope so we can
      // access from them from our entire application
      $rootScope.$state = $state;
      $rootScope.$stateParams = $stateParams;

      // Same here
      $rootScope.basePath = SETTINGS.basePath;
      $rootScope.viewsPath = SETTINGS.viewsPath;
      $rootScope.assetsPath = SETTINGS.assetsPath;

      $rootScope.headerPartialPath = SETTINGS.viewsPath + '/layout/header.html';
      $rootScope.footerPartialPath = SETTINGS.viewsPath + '/layout/footer.html';

      var allowedNames = ['login', '404'];
      $rootScope.$on('$stateChangeStart', function (event, toState) {
        if (allowedNames.indexOf(toState.name) === -1 && !AuthenticationService.isAuthenticated()) {
          event.preventDefault();
          $state.go('login');
        }
      });

    });

})();
