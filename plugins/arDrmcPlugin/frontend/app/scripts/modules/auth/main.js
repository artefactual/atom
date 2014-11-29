(function () {

  'use strict';

  angular.module('drmc.modules.auth')

    /**
     * AUTH_EVENTS is a dictionary containing the different types of events used
     * under this module.
     */
    .constant('AUTH_EVENTS', {
      loginRequired: 'login-required'
    })

    /**
     * Intercept AUTH_EVENTS.login-required. I have a chance here to install a
     * http interceptor temporary to avoid more HTTP requests going on. No need
     * to overwhelm the server (TODO).
     */
    .run(function ($rootScope, $state, AUTH_EVENTS) {
      $rootScope.isLogging = false;
      $rootScope.$on(AUTH_EVENTS.loginRequired, function () {
        // Do nothing if we are already on it (many requests in a controller may
        // result in throwing the same event multiple times)
        if ($rootScope.isLogging) {
          return;
        }
        $rootScope.isLogging = true;
        $state.go('login');
      });
    });

})();
