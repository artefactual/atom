'use strict';

module.exports = function ($scope, $modal, $modalInstance, files, ModalDigitalObjectViewerService) {

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

  $scope.download = function () {

  };

  // Defaults for the sidebar.
  // Notice that ng-repeat will inherit this and then rewrite
  $scope.showAipsArea = false;
  $scope.showFitsArea = false;
  $scope.showMediaArea = true;

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
    $scope.files.splice(index, 1);
    // TODO: destroy scope
    // TODO: update pages
    // TODO: update current
  };

  $scope.showPrev = function () {
    return $scope.page > 1;
  };

  $scope.showNext = function () {
    return $scope.page < $scope.total;
  };

};
