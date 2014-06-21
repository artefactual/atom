(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.modules')

    /**
     * Simplified version of witoldsz/angular-http-auth without HttpBuffer.
     */

    /**
     * AUTH_EVENTS is a dictionary containing the different types of events used
     * under this module.
     */
    .constant('AUTH_EVENTS', {
      loginRequired: 'login-required'
    })

    /**
     * AuthInterceptorService will broadcast AUTH_EVENTS.login-required whenever
     * a HTTP 401 response is intercepted.
     */
    .factory('AuthInterceptorService', require('./AuthInterceptorService'))
    .config(function ($httpProvider) {
      $httpProvider.interceptors.push('AuthInterceptorService');
    })

    /**
     * Intercept AUTH_EVENTS.login-required. I have a chance here to install a
     * http interceptor temporary to avoid more HTTP requests going on. TODO.
     */
    .run(function ($rootScope, $state, AUTH_EVENTS) {
      $rootScope.$on(AUTH_EVENTS.loginRequired, function () {
        $state.go('login');
      });
    })

    /**
     * AuthenticationService configures the $http provider and validates your
     * credentials, also logs you out when required and creates the user object.
     */
    .service('AuthenticationService', require('./AuthenticationService'));

})();
