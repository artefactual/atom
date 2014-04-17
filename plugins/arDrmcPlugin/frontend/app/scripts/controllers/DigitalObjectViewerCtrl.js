'use strict';

module.exports = function ($scope, $q, $modal, $stateParams, $modalInstance, files, InformationObjectService, ModalDigitalObjectViewerService) {

  // Share files with the model
  // Make sure that we convert into array when files is just one object
  if (angular.isArray(files)) {
    $scope.files = files;
  } else {
    $scope.files = [files];
  }

  // Get class by media type
  $scope.getMediaTypeCssClass = function (file) {
    return ModalDigitalObjectViewerService.mediaTypes[file.media_type_id].class;
  };

  $scope.page = 1;
  $scope.total = $scope.files.length;
  $scope.current = $scope.files[$scope.page - 1];

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  $scope.download = function (itemFile) {
    var itemId = itemFile.id;

    InformationObjectService.getById(itemId).then(function (response) {
      $scope.itemToDownload = response;
    });
    // TODO: Finish download work
  };

  // Defaults for the sidebar.
  // Notice that ng-repeat will inherit this and then rewrite
  $scope.showAipsArea = true;
  $scope.showFitsArea = false;
  $scope.showMediaArea = false;

  $scope.prev = function () {
    if ($scope.page < 2) {
      return;
    }
    var prevPage = $scope.page - 2;
    $scope.current = $scope.files[prevPage];
    $scope.page--;
  };

  $scope.next = function () {
    if ($scope.page > $scope.total - 1) {
      return;
    }
    $scope.current = $scope.files[$scope.page++];
  };

  $scope.select = function (item) {
    $scope.current = item;
    // Update page
    for (var i = 0; i < $scope.total; i++) {
      if ($scope.files[i] === item) {
        $scope.page = i + 1;
        break;
      }
    }
  };

  $scope.unselect = function (index) {
    if($scope.files.length > 1) {
      $scope.files.splice(index, 1);
    } else {
      $modalInstance.dismiss('cancel');
    }
  };

  $scope.showPrev = function () {
    return $scope.page > 1;
  };

  $scope.showNext = function () {
    return $scope.page < $scope.total;
  };

  // Get list of files for compare view
  InformationObjectService.getById($stateParams.id).then(function (response) {

    var deferred = $q.defer();

    setTimeout(function () {
      // $q so browser can load viewer first before loading all files
      InformationObjectService.getDigitalObjects($stateParams.id).then(function (response) {
        return response;
      });

      deferred.resolve(response);
    }, 750);

    return deferred.promise;
  });
};
