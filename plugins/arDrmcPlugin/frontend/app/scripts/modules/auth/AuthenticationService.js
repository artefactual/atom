(function () {

  'use strict';

  var User = window.User;

  /**
   * AuthenticationService configures the $http provider and validates your
   * credentials, also logs you out when required and creates the user object.
   */

  angular.module('drmc.modules.auth').service('AuthenticationService', function ($rootScope, $http, $state, SETTINGS) {

    // Notice that a reference to the user object (User) is stored in
    // $rootScope.user so it can be used across all our models. An alternative
    // would be to have an extra service like UserService maybe?
    this.user = undefined;

    // Users endpoint
    var url = SETTINGS.frontendPath + 'api/users/authenticate';

    this.validate = function (username, password) {
      var self = this;
      return $http({
        method: 'POST',
        url: url,
        data: {
          username: username,
          password: password
        }
      }).success(function (data) {
        $rootScope.user = self.user = new User(data);
      });
    };

    this.load = function () {
      var self = this;
      return $http({
        method: 'GET',
        url: url
      }).success(function (data) {
        $rootScope.user = self.user = new User(data);
      });
    };

    this.logOut = function () {
      var self = this;
      return $http({
        method: 'DELETE',
        url: url
      }).success(function () {
        delete self.user;
        delete $rootScope.user;
        $state.go('login');
      });
    };

  });

})();
