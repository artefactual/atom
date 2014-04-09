'use strict';

module.exports = function ($cookies, $http, $q, SETTINGS) {

  // use promise to deal with authentication success or failure
  this.isAuthenticated = function () {
    // if not logged in, attempt authentication
    if (typeof $cookies['drmc_username'] === 'undefined') {
      var deferred = $q.defer();

      // request authorization
      $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/users/authenticate',
        params: {
          username: 'mcantelon@gmail.com',
          password: 'mike'
        }
      })
        .success(function(response) {
          if (typeof response['error'] == 'undefined') {
            deferred.resolve();
          } else {
            deferred.reject(response['error']);
          }
        })
        .error(function(data) {
          // a problem occurred attempting authentication
          deferred.reject('Unable to make authentication request');
        })

      // return promise that will either resolve or be rejected
      return deferred.promise;
    } else {
      // logged in, so return resolved promise
      var deferred = $q.defer();
      deferred.resolve();
      return deferred.promise;
    }
  };

};
