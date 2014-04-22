'use strict';

module.exports = function ($scope, $modal, SETTINGS, $stateParams, AIPService, InformationObjectService, ModalDigitalObjectViewerService, ModalDownloadService, ModalReclassifyAipService) {

  AIPService.getAIP($stateParams.uuid)
    .success(function (data) {
      $scope.aip = data;
      pullFiles();
    });

  $scope.openReclassifyModal = function () {
    ModalReclassifyAipService.open($scope.aip.uuid, $scope.aip.part_of.title).result.then(function (data) {
      $scope.aip.type.id = data.type_id;
      $scope.aip.type.name = data.type;
    });
  };


  /**
   * Interaction with modals
   */

  $scope.downloadFile = function (aipFile) {
    ModalDownloadService.downloadFile($scope.aip.name, $scope.aip.uuid, aipFile.originalRelativePathWithinAip);
  };

  $scope.downloadAip = function () {
    ModalDownloadService.downloadAip($scope.aip.name, $scope.aip.uuid);
  };

  $scope.openViewer = function () {
    ModalDigitalObjectViewerService.open();
  };


  /**
   * File list widget
   */

  $scope.criteria = {};
  $scope.criteria.limit = 4;
  $scope.criteria.sort = 'name';
  $scope.page = 1;
  $scope.files = [];

  var pullFiles = function () {
    InformationObjectService.getDigitalObjects($scope.aip.part_of.id, false, $scope.criteria)
      .success(function (data) {
        $scope.files = data.results;
        $scope.$broadcast('pull.success', data.total);
      });
  };

  // Watch for criteria changes
  $scope.$watch('criteria', function () {
    if (!$scope.files.length) {
      return;
    }
    pullFiles();
  }, true);

  // Changes in scope.page updates criteria.skip
  $scope.$watch('page', function (value) {
    $scope.criteria.skip = (value - 1) * $scope.criteria.limit;
  });

};
