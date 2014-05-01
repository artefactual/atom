'use strict';

module.exports = function ($http, $q, SETTINGS) {
  var authenticationUrl = SETTINGS.frontendPath + 'api/users/authenticate',
      self = this;

  // user data includes helpers to make privilege checking in templates easier
  function setUserData (user) {
    self.user = user;

    self.user.isMemberOf = function (group) {
      return self.user.groups.indexOf(group) !== -1;
    };

    self.user.isMemberOfOneOf = function (groups) {
      var result = false;
      groups.forEach(function (group) {
        if (self.user.isMemberOf(group)) {
          result = true;
        }
      });
      return result;
    };

    self.user.canAdministrate = function () {
      return self.user.isMemberOf('administrator');
    };

    self.user.canEdit = function () {
      return self.user.isMemberOfOneOf([
        'administrator',
        'editor'
      ]);
    };

    self.user.canContribute = function () {
      return self.user.canEdit() || self.user.isMemberOf('contributors');
    };

    self.user.canRead = function () {
      return self.user.isMemberOf('authenticated');
    };
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
