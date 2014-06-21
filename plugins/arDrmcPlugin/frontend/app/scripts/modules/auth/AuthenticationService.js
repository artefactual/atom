(function () {

  'use strict';

  var User = require('../../lib/user');

  module.exports = function ($rootScope, $http, $state, SETTINGS, $window, $sessionStorage, $q) {

    // Notice that a reference to the user object (User) is stored in
    // $rootScope.user so it can be used across all our models. An alternative
    // would be to have an extra service like UserService maybe?
    this.user = undefined;

    this.validate = function (username, password) {
      // Setup new credentials before trying
      addAuthHeader(username, password);
      var self = this;
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/users/authenticate',
        // This is very important as it bypasses the interceptor defined in
        // AuthInterceptorService in order to avoid triggering the callbacks
        // when a login is required, as that's what we are trying to do here.
        ignoreAuthModule: true
      }).success(function (data) {
        $rootScope.user = self.user = new User(data);
        $sessionStorage.credentials = {
          username: username,
          password: password
        };
      }).error(function () {
        removeAuthHeader();
      });
    };

    this.logOut = function () {
      removeAuthHeader();
      delete this.user;
      delete $rootScope.user;
      delete $sessionStorage.credentials;
      $state.go('login');
    };

    this.restoreSession = function () {
      var deferred = $q.defer();
      console.log($sessionStorage.credentials);
      if (angular.isUndefined($sessionStorage.credentials)) {
        deferred.reject();
        return deferred.promise;
      }
      var credentials = $sessionStorage.credentials;
      this.validate(credentials.username, credentials.password).then(function () {
        deferred.resolve();
      }, function () {
        deferred.reject();
        delete $sessionStorage.credentials;
      });
      return deferred.promise;
    };

    var addAuthHeader = function (username, password) {
      $http.defaults.headers.common.Authorization = 'Basic ' + $window.btoa(username + ':' + password);
    };

    var removeAuthHeader = function () {
      delete $http.defaults.headers.common.Authorization;
    };

  };

})();
