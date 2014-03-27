'use strict';

module.exports = function ($scope, $stateParams, $modal, SETTINGS, InformationObjectService) {

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
    var modalInstance = $modal.open({
      templateUrl: SETTINGS.viewsPath + '/modals/edit-dc-metadata.html',
      backdrop: true,
      controller: 'EditDCMetadataCtrl',
      resolve: {
        resource: function () {
          return $scope.techRecord;
        }
      }
    });
    // Pull again when the modal succeeds
    modalInstance.result.then(function () {
      $scope.pull();
    });
  };

};
