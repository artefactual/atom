'use strict';

module.exports = function ($scope, $state, md5, SETTINGS, ModalEditDcMetadataService, AuthenticationService) {

  $scope.openEditDcModal = function () {
    ModalEditDcMetadataService.create();
  };

  $scope.logOut = function () {
    AuthenticationService.logOut();
    $state.go('login');
  };

};
