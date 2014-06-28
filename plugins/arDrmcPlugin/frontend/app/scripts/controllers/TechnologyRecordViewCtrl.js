'use strict';

module.exports = function ($scope, $stateParams, InformationObjectService, ModalEditDcMetadataService) {

  $scope.pull = function () {
    InformationObjectService.getSupportingTechnologyRecord($stateParams.id).then(function (response) {
      $scope.techRecord = response.data;
    }, function (reason) {
      throw reason;
    });
  };

  // Pull during initialization
  $scope.pull();

  // A list of digital objects. This is shared within the context browser
  // directive (two-way binding);
  $scope.files = [];

  $scope.selectNode = function () {

  };

  // Edit metadata of the current technology record
  $scope.edit = function () {
    ModalEditDcMetadataService.edit($scope.techRecord.id).result.then(function () {
      $scope.pull();
      $scope.$broadcast('reload');
    });
  };

  // Add new child
  $scope.addChild = function () {
    ModalEditDcMetadataService.create($scope.techRecord.id).result.then(function () {
      $scope.pull();
      $scope.$broadcast('reload');
    });
  };

};
