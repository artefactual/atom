'use strict';

module.exports = function ($scope, AuthenticationService, $state) {

  $scope.form = {};

  $scope.notifyOfError = function ($error) {
    window.alert($error);
  };

  $scope.submit = function () {
    console.log('UN:' + $scope.form.username);
    console.log('PW:' + $scope.form.password);
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
