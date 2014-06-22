'use strict';

module.exports = function ($scope, $state, AuthenticationService) {

  $scope.submit = function () {
    if ($scope.form.$invalid) {
      return;
    }
    $scope.loading = true;
    // TODO: Make use of FormController to validate the form as part of the
    // authentication process (see $setValidity(), etc...)
    AuthenticationService.validate($scope.username, $scope.password)
      .then(function () {
        $state.go('main.dashboard');
      }, function () {
        $scope.error = 'Incorrect username or password.';
      }).finally(function () {
        $scope.loading = false;
        delete $scope.username;
        delete $scope.password;
      });
  };

};
