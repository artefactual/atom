'use strict';

module.exports = function ($cookies, $http, $q, SETTINGS) {
  var authenticationUrl = SETTINGS.frontendPath + 'api/users/authenticate',
      self = this;

  this.authenticate = function (username, password) {
    return $http({
      method: 'POST',
      url: authenticationUrl,
      data: {
        username: username,
        password: password
      }
    })
    .success(function (user) {
      self.user = user;
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
    })
    .success(function () {
      delete self.user;
    });
  };
};
