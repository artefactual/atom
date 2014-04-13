'use strict';

module.exports = function ($scope, $state, SETTINGS, ModalEditDcMetadataService, AuthenticationService) {

  $scope.openEditDcModal = function () {
    ModalEditDcMetadataService.create();
  };

  $scope.logOut = function () {
    AuthenticationService.logOut();
    $state.go('login');
  };

  var getCurrentUser = function () {
    return AuthenticationService.user;
  };

  $scope.$watch(getCurrentUser, function (user) {
    if (typeof user !== 'undefined') {
      // TODO: use md5 to figure out gravatar ID
      // TODO: is there a user human name we can pull from a profile?
      $scope.user = {
        username: user.username,
        email:    user.email,
        gravatar: 'http://www.gravatar.com/avatar/ab6aae7aa48f96cf5ea5ab5d3aa2247d?s=25'
      };
    } else {
      delete $scope.user;
    }
  });
};
