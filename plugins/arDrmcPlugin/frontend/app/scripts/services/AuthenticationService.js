'use strict';

module.exports = function ($cookies, $http, $q, SETTINGS) {
  var authenticationUrl = SETTINGS.frontendPath + 'api/users/authenticate';

  this.authenticate = function (username, password) {
    return $http({
      method: 'POST',
      url: authenticationUrl,
      params: {
        username: 'demo@example.com',
        password: 'demo'
      }
    })
  };

  this.isAuthenticated = function () {
    return $http({
      method: 'GET',
      url: authenticationUrl
    })
  };
};
