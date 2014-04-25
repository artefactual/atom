'use strict';

module.exports = function ($http, $q, SETTINGS) {
  var authenticationUrl = SETTINGS.frontendPath + 'api/users/authenticate',
      self = this;

  function setUserData (user) {
    self.user = user;
  }

  this.authenticate = function (username, password) {
    return $http({
      method: 'POST',
      url: authenticationUrl,
      data: {
        username: username,
        password: password
      }
    })
    .success(setUserData);
  };

  this.isAuthenticated = function () {
    return $http({
      method: 'GET',
      url: authenticationUrl
    })
    .success(setUserData);
  };

  this.isMemberOf = function(group) {
    return self.user.groups.indexOf(group) != -1;
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
