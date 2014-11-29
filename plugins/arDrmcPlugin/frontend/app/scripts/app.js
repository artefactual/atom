(function () {

  'use strict';

  /*
   * Define module and dependencies
   */

  angular.module('drmc', [

    'drmc.services',
    'drmc.directives',
    'drmc.controllers',
    'drmc.filters',
    'drmc.modules',

    'ui.router',
    'ui.bootstrap',
    'cfp.hotkeys',
    'ngStorage'

  ]);


  /*
   * Kickstart the application
   *
   * This is executed after all the services have been configured and the injector
   * has been created.
   */

  angular.module('drmc')
    .run(function ($rootScope, SETTINGS, $state, $stateParams) {

      // Share information with $rootScope so it's globally available from our views
      $rootScope.$state = $state;
      $rootScope.$stateParams = $stateParams;
      $rootScope.basePath = SETTINGS.basePath;
      $rootScope.viewsPath = SETTINGS.viewsPath;
      $rootScope.assetsPath = SETTINGS.assetsPath;

    });

})();
