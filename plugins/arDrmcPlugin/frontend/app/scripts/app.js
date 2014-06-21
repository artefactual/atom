(function () {

  'use strict';

  var angular = require('angular');

  /*
   * Define module and dependencies
   */

  angular.module('momaApp', [

    require('./services').name,
    require('./directives').name,
    require('./controllers').name,
    require('./filters').name,
    require('./modules').name,

    'ui.router',
    'ui.bootstrap',
    'cfp.hotkeys',
    'ngStorage'

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
    .run(function ($rootScope, SETTINGS, $state, $stateParams) {

      // Share information with $rootScope so it's globally available from our views
      $rootScope.$state = $state;
      $rootScope.$stateParams = $stateParams;
      $rootScope.basePath = SETTINGS.basePath;
      $rootScope.viewsPath = SETTINGS.viewsPath;
      $rootScope.assetsPath = SETTINGS.assetsPath;

    });

})();
