'use strict';

module.exports = function ($scope, AuthenticationService) {

  $scope.form = {};

  $scope.submit = function() {
    if ($scope.form.username && $scope.form.password) {
      AuthenticationService.authenticate($scope.form.username,  $scope.form.password);
    } else {
      alert('Please enter both a username and a password.');
    }
  };
};
