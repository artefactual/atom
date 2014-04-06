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
        console.log($scope.modalContainer.reason);
        $modalInstance.close($scope.modalContainer.reason);
      };

      $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
      };
    }
  };

  var open = function (aip, uuid, relativePathWithinAip) {
    return $modal.open(configuration).result.then(function (reason) {
      var url = '/api/aips/' + uuid + '/download?reason=' + $window.encodeURIComponent(reason);
      if (typeof relativePathWithinAip !== 'undefined') {
        url += '&relative_path_to_file=' + $window.encodeURIComponent(relativePathWithinAip);
      }
      $window.open(url, '_blank');
    });
  };

  this.downloadFile = function (aip, uuid, relativePathWithinAip) {
    configuration.resolve.title = function () {
      return 'Download file';
    };
    configuration.resolve.downloadDescription = function () {
      return relativePathWithinAip;
    };
    return open(aip, uuid, relativePathWithinAip);
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
