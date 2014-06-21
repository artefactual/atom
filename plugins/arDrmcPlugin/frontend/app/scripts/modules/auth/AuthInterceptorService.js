(function () {

  'use strict';

  module.exports = function ($rootScope, $q, AUTH_EVENTS) {

    return {
      responseError: function (response) {
        if (response.status === 401 && !response.config.ignoreAuthModule) {
          var deferred = $q.defer();
          $rootScope.$broadcast(AUTH_EVENTS.loginRequired, response);
          return deferred.promise;
        }
        return $q.reject(response);
      }
    };

  };

})();
