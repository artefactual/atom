(function () {

  'use strict';

  angular.module('drmc.controllers').controller('TechnologyRecordViewCtrl', function ($scope, $stateParams, InformationObjectService, ModalEditDcMetadataService, ModalDigitalObjectViewerService, ModalDownloadService) {

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
      InformationObjectService.getAips($stateParams.id).then(function (data) {
        $scope.aggregation = data.overview;
      });
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


    // TODO: downloadFile, downloadAip and openDigitalObjectModal is used both in
    // WorkViewCtrl and TechnologyRecordViewctrl, inside aip-overview.html. Create
    // a directive that can be shared instead of having duplicated functionality.

    $scope.downloadFile = function (file) {
      ModalDownloadService.downloadFile(
        file.aip_name,
        file.aip_uuid,
        file.id,
        file.original_relative_path_within_aip
      );
    };

    $scope.downloadAip = function (file) {
      ModalDownloadService.downloadAip(file.aip_name, file.aip_uuid);
    };

    $scope.openDigitalObjectModal = function (files, index) {
      ModalDigitalObjectViewerService.open(files, index);
    };

  });

})();
