(function () {

  'use strict';

  module.exports = function ($rootScope, $q, AUTH_EVENTS, HttpBufferService) {

    return {
      responseError: function (response) {
        console.log(response.status);
        if (response.status === 401 && !response.config.ignoreAuthModule) {
          var deferred = $q.defer();
          HttpBufferService.append(response.config, deferred);
          $rootScope.$broadcast(AUTH_EVENTS.loginRequired, response);
          return deferred.promise;
        }
        return $q.reject(response);
      }
    };

  };

})();
