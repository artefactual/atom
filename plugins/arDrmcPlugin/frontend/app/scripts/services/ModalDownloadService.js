'use strict';

module.exports = function ($modal, $window, SETTINGS) {
  var configuration = {
    templateUrl: SETTINGS.viewsPath + '/modals/download-aip-or-aip-file.html',
    backdrop: true,
    resolve: {},
    controller: function ($scope, $document, $window, $modalInstance, downloadDescription, title) {
      // Hack transclude problem in modals/angularjs, see:
      // https://github.com/angular-ui/bootstrap/issues/969#issuecomment-31875867
      // https://github.com/angular-ui/bootstrap/issues/969#issuecomment-33128068
      $scope.modalContainer = {};

      $scope.minLength = 10;

      // Resolved
      $scope.downloadDescription = downloadDescription;
      $scope.title = title;

      $scope.submit = function () {
        if (!$scope.modalContainer.form.$valid) {
          return;
        }
        $modalInstance.close($scope.modalContainer.reason);
      };

      $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
      };
    }
  };

  var open = function (aip, uuid, fileId) {
    return $modal.open(configuration).result.then(function (reason) {
      var url = '/api/aips/' + uuid + '/download?reason=' + $window.encodeURIComponent(reason);
      if (typeof fileId !== 'undefined') {
        url += '&file_id=' + $window.encodeURIComponent(fileId);
      }
      $window.open(url, '_blank');
    });
  };

  this.downloadFile = function (aip, uuid, fileId, fileDescription) {
    configuration.resolve.title = function () {
      return 'Download file';
    };
    configuration.resolve.downloadDescription = function () {
      return fileDescription;
    };
    return open(aip, uuid, fileId);
  };

  this.downloadAip = function (aip, uuid) {
    configuration.resolve.title = function () {
      return 'Download AIP';
    };
    configuration.resolve.downloadDescription = function () {
      return 'AIP ' + aip + ' (' + uuid + ')';
    };
    return open(aip, uuid);
  };
};
