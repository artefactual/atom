'use strict';

module.exports = function ($scope, $window, AuthenticationService, $state) {

  function notifyOfError ($error) {
    $window.alert($error);
  }

  $scope.submit = function () {
    if (!$scope.loginForm.$valid) {
      notifyOfError('Please enter both a username and a password.');
      return;
    }
    // TODO: Make use of FormController to validate the form as part of the
    // authentication process (see $setValidity(), etc...)
    AuthenticationService.authenticate($scope.username, $scope.password)
      .success(function () {
        $state.go('main.dashboard');
      }).error(function () {
        notifyOfError('Incorrect username or password.');
      });
  };

};
