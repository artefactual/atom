'use strict';

module.exports = function ($scope, AuthenticationService) {

  $scope.form = {};

  $scope.notifyOfError = function () {
    window.alert('Please enter both a username and a password.');
  };

  $scope.submit = function () {
    if ($scope.form.username && $scope.form.password) {
      AuthenticationService.authenticate($scope.form.username,  $scope.form.password)
      .success(function () {

      })
      .error(function () {
        $scope.notifyOfError();
      });
    } else {
      $scope.notifyOfError();
    }
  };
};
