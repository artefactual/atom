'use strict';

module.exports = function ($cookies, $http, $q, SETTINGS) {
  var authenticationUrl = SETTINGS.frontendPath + 'api/users/authenticate';

  this.authenticate = function (username, password) {
    return $http({
      method: 'POST',
      url: authenticationUrl,
      data: {
        username: username,
        password: password
      }
    });
  };

  this.isAuthenticated = function () {
    return $http({
      method: 'GET',
      url: authenticationUrl
    });
  };

  this.logOut = function () {
    return $http({
      method: 'DELETE',
      url: authenticationUrl
    });
  };
};
