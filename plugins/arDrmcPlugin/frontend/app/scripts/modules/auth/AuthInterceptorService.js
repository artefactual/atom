(function () {

  'use strict';

  /**
   * AuthInterceptorService will broadcast AUTH_EVENTS.login-required whenever
   * a HTTP 401 response is intercepted.
   */

  angular.module('drmc').config(function ($httpProvider) {
    $httpProvider.interceptors.push('AuthInterceptorService');
  });

  angular.module('drmc.modules.auth').factory('AuthInterceptorService', function ($rootScope, $q, AUTH_EVENTS) {
    return {
      responseError: function (response) {
        if (response.status === 401) {
          var deferred = $q.defer();
          $rootScope.$broadcast(AUTH_EVENTS.loginRequired, response);
          return deferred.promise;
        }
        return $q.reject(response);
      }
    };
  });

})();
