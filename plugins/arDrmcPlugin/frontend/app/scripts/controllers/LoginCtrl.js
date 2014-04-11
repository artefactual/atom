'use strict';

module.exports = function ($scope, AuthenticationService, $state) {

  $scope.form = {};

  $scope.notifyOfError = function () {
    window.alert('Please enter both a username and a password.');
  };

  $scope.submit = function () {
    if ($scope.form.username && $scope.form.password) {
      AuthenticationService.authenticate($scope.form.username,  $scope.form.password)
      .success(function () {
        $state.go('dashboard');
      })
      .error(function () {
        $scope.notifyOfError();
      });
    } else {
      $scope.notifyOfError();
    }
  };
};
