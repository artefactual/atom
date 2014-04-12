'use strict';

module.exports = function ($scope, $window, AuthenticationService, $state) {

  $scope.form = {};

  function notifyOfError ($error) {
    $window.alert($error);
  }

  $scope.submit = function () {
    if ($scope.form.username && $scope.form.password) {
      AuthenticationService.authenticate($scope.form.username,  $scope.form.password)
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
