'use strict';

module.exports = function ($scope, $window, AuthenticationService, $state) {

  $scope.form = {};

  $scope.notifyOfError = function ($error) {
    $window.alert($error);
  };

  $scope.submit = function () {
    if ($scope.form.username && $scope.form.password) {
      AuthenticationService.authenticate($scope.form.username,  $scope.form.password)
      .success(function () {
        $state.go('dashboard');
      })
      .error(function () {
        $scope.notifyOfError('Incorrect username or password.');
      });
    } else {
      $scope.notifyOfError('Please enter both a username and a password.');
    }
  };
};
