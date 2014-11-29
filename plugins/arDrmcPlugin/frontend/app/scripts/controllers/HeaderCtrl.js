(function () {

  'use strict';

  angular.module('drmc.controllers').controller('HeaderCtrl', function ($rootScope, $scope, $state, SETTINGS, ModalEditDcMetadataService, AuthenticationService) {

    $scope.openEditDcModal = function () {
      ModalEditDcMetadataService.create();
    };

    $scope.logOut = function () {
      AuthenticationService.logOut();
      $state.go('login');
    };

    if (angular.isUndefined($rootScope.user)) {
      AuthenticationService.load();
    }

  });

})();
