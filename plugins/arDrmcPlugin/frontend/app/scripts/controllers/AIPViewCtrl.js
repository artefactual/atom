'use strict';

module.exports = function ($scope, $modal, SETTINGS, $stateParams, AIPService, InformationObjectService, ModalDigitalObjectViewerService) {

  $scope.openDownloadModal = function (relativePathWithinAip) {
    // $modal.open returns a promise
    var modalInstance = $modal.open({
      templateUrl: SETTINGS.viewsPath + '/modals/download-aip-or-aip-file.html',
      backdrop: true,
      scope: $scope, // TODO: isolate with .new()?
    });
    // This is going to happen only if the $modal succeeded
    modalInstance.result.then(function (reason) {
      var downloadUrl = '/api/aips/' + $scope.aip.uuid + '/download?reason=' + encodeURIComponent(reason);

      if (typeof relativePathWithinAip !== 'undefined') {
        downloadUrl += '&relative_path_to_file=' + encodeURIComponent(relativePathWithinAip);
      }

      window.location = downloadUrl;
    });

    $scope.download = function (reason) {
      modalInstance.close(reason);
    };

    $scope.cancel = function () {
      modalInstance.dismiss('cancel');
    };
  };

  AIPService.getAIP($stateParams.uuid)
    .success(function (data) {
      $scope.aip = data;

      $scope.openViewer = function () {
        ModalDigitalObjectViewerService.open();
      };

      window.scope = $scope;
      // criteria contain GET params used when calling getFiles to refresh data
      $scope.criteria = {};
      $scope.criteria.limit = 3;
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
