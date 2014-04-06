'use strict';

module.exports = function ($scope, $modal, SETTINGS, $stateParams, AIPService, InformationObjectService, ModalDigitalObjectViewerService, ModalDownloadService) {

  $scope.downloadFile = function (aipFile) {
    ModalDownloadService.downloadFile($scope.aip.name, $scope.aip.uuid, aipFile.originalRelativePathWithinAip);
  };

  $scope.downloadAip = function () {
    ModalDownloadService.downloadAip($scope.aip.name, $scope.aip.uuid);
  };

  AIPService.getAIP($stateParams.uuid)
    .success(function (data) {
      $scope.aip = data;

      $scope.openViewer = function () {
        ModalDigitalObjectViewerService.open();
      };

      // criteria contain GET params used when calling getFiles to refresh data
      $scope.criteria = {};
      $scope.criteria.limit = 10;
      $scope.criteria.sort = 'name';
      $scope.page = 1; // Don't delete this, it's an important default for the loop

      // Changes in scope.page updates criteria.skip
      $scope.$watch('page', function (value) {
        $scope.criteria.skip = (value - 1) * $scope.criteria.limit;
      });

      // Watch for criteria changes
      $scope.$watch('criteria', function () {
        $scope.pull();
      }, true); // check properties when watching

      $scope.pull = function () {
        // retrieve AIP files
        InformationObjectService.getDigitalObjects(data.part_of.id, false, $scope.criteria)
          .success(function (data) {
            $scope.aipFiles = data.results;
            $scope.$broadcast('pull.success', data.total);
          });
      };
    });
};
