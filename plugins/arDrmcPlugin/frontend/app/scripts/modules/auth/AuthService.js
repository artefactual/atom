(function () {

  'use strict';

  module.exports = function ($rootScope, AUTH_EVENTS, HttpBufferService) {

    return {

      /**
       * Call this function to indicate that authentication was successfull and
       * trigger a retry of all deferred requests.
       *
       * param data an optional argument to pass on to $broadcast which may be useful for
       * example if you need to pass through details of the user that was logged in
       */
      loginConfirmed: function (data, configUpdater) {
        var updater = configUpdater || function (config) { return config; };
        $rootScope.$broadcast(AUTH_EVENTS.loginConfirmed, data);
        HttpBufferService.retryAll(updater);
      },

      /**
       * Call this function to indicate that authentication should not proceed.
       * All deferred requests will be abandoned or rejected (if reason is
       * provided).
       *
       * param data an optional argument to pass on to $broadcast.
       * param reason if provided, the requests are rejected; abandoned otherwise.
       */
      loginCancelled: function (data, reason) {
        HttpBufferService.rejectAll(reason);
        HttpBufferService.$broadcast(AUTH_EVENTS.loginCancelled, data);
      }

    };

  };

})();
