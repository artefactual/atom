'use strict';

module.exports = function ($scope, $state, AuthenticationService) {

  $scope.submit = function () {
    if ($scope.form.$invalid) {
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
