'use strict';

module.exports = function ($scope, $window, AuthenticationService, $state) {

  function notifyOfError ($error) {
    $window.alert($error);
  }

  $scope.submit = function () {
    if ($scope.loginForm.$valid) {
      AuthenticationService.authenticate($scope.username, $scope.password)
      .success(function () {
        $state.go('dashboard');
      })
      .error(function () {
        notifyOfError('Incorrect username or password.');
      });
    } else {
      notifyOfError('Please enter both a username and a password.');
    }
  };
};
