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

  angular.module('momaApp').constant('ATOM_CONFIG', require('./settings'));


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
      $rootScope.basePath = ATOM_CONFIG.basePath;
      $rootScope.viewsPath = ATOM_CONFIG.viewsPath;
      $rootScope.assetsPath = ATOM_CONFIG.assetsPath;

      $rootScope.headerPartialPath = ATOM_CONFIG.viewsPath + '/layout/header.html';
      $rootScope.footerPartialPath = ATOM_CONFIG.viewsPath + '/layout/footer.html';

    });

})();
