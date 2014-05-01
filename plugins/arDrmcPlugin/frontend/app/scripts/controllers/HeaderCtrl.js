'use strict';

module.exports = function ($scope, $state, md5, SETTINGS, ModalEditDcMetadataService, AuthenticationService) {

  $scope.openEditDcModal = function () {
    ModalEditDcMetadataService.create();
  };

  $scope.logOut = function () {
    AuthenticationService.logOut();
    $state.go('login');
  };

  // Get current user details/privileges
  var getCurrentUser = function () {
    return AuthenticationService.user;
  };

  $scope.$watch(getCurrentUser, function (user) {
    if (typeof user !== 'undefined') {
      $scope.user = user;
      $scope.gravatar = 'http://www.gravatar.com/avatar/7c4ff521986b4ff8d29440beec01972d?s=25';
    } else {
      delete $scope.user;
    }
  });
};
