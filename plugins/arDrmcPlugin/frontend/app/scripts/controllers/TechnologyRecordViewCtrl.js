'use strict';

module.exports = function ($scope, $stateParams, $modal, SETTINGS, InformationObjectService, ModalEditDcMetadataService) {

  $scope.pull = function () {
    InformationObjectService.getSupportingTechnologyRecord($stateParams.id).then(function (response) {
      $scope.techRecord = response.data;
    }, function (reason) {
      throw reason;
    });
  };

  // Pull during initialization
  $scope.pull();

  $scope.openEditDcModal = function () {
    var modal = ModalEditDcMetadataService.edit($scope.techRecord);
    // Pull again when the modal succeeds
    modal.result.then(function () {
      $scope.pull();
    });
  };

};
