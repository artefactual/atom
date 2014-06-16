(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.modules')

    .factory('AuthService', require('./AuthService'))
    .factory('AuthInterceptorService', require('./AuthInterceptorService'))
    .factory('HttpBufferService', require('./HttpBufferService'))

    .constant('AUTH_EVENTS', {
      loginRequired: 'auth-login-required',
      loginCancelled: 'auth-login-cancelled',
      loginConfirmed: 'auth-login-confirmed'
    })

    .config(function ($httpProvider) {
      $httpProvider.interceptors.push('AuthInterceptorService');
    })

    .run(function ($rootScope, $state) {
      $rootScope.$on('auth-login-required', function () {
        $state.go('login');
      });
    });

})();
