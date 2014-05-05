'use strict';

var User = require('../lib/user');

module.exports = function ($rootScope, $http, SETTINGS) {

  var authenticationUrl = SETTINGS.frontendPath + 'api/users/authenticate';

  // Notice that a reference to the user object (User) is stored in
  // $rootScope.user so it can be used across all our models. An alternative
  // would be to have an extra service like UserService maybe?

  this.authenticate = function (username, password) {
    var self = this;
    return $http({
      method: 'POST',
      url: authenticationUrl,
      data: {
        username: username,
        password: password
      }
    }).success(function (data) {
      $rootScope.user = self.user = new User(data);
    });
  };

  this.isAuthenticated = function () {
    var self = this;
    return $http({
      method: 'GET',
      url: authenticationUrl
    }).success(function (data) {
      $rootScope.user = self.user = new User(data);
    });
  };

  this.logOut = function () {
    var self = this;
    return $http({
      method: 'DELETE',
      url: authenticationUrl
    })
    .success(function () {
      delete $rootScope.user;
      delete self.user;
    });
  };

};
