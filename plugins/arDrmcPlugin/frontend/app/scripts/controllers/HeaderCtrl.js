'use strict';

module.exports = function ($scope, $state, SETTINGS, ModalEditDcMetadataService, AuthenticationService) {

  $scope.openEditDcModal = function () {
    ModalEditDcMetadataService.create();
  };

  $scope.logOut = function () {
    AuthenticationService.logOut();
    $state.go('login');
  };

};
