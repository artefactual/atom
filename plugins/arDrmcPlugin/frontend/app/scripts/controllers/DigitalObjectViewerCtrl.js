'use strict';

module.exports = function ($scope, $q, $modal, $stateParams, $modalInstance, hotkeys, InformationObjectService, ModalDigitalObjectViewerService, files, index) {

  // Full collection of files that the viewer is browsing
  $scope.files = files;

  // Set page and current file based in the index parameter, which may be not
  // defined when the user is not visualizing a file in particular
  index = index === null ? 0 : index;
  $scope.current = $scope.files[index];
  $scope.page = index + 1;
  $scope.total = $scope.files.length;

  // Return CSS class give file.media_type_id obtained from the mediaTypes
  // object available in ModalDigitalObjectViewerService
  $scope.getMediaTypeCssClass = function (file) {
    if (angular.isUndefined(file) || angular.isUndefined(file.media_type_id)) {
      return;
    }
    var mt = ModalDigitalObjectViewerService.mediaTypes[file.media_type_id];
    if (angular.isDefined(mt) && angular.isDefined(mt.class)) {
      return 'drmc-icon-' + mt.class;
    }
  };

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  // Download files
  $scope.download = function (file) {
    console.log(file, 'TODO');
  };


  /**
   * Pager and selection
   */

  $scope.prev = function () {
    if ($scope.page < 2) {
      return;
    }
    $scope.current = $scope.files[--$scope.page - 1];
    $scope.comparingFiles = [$scope.current];
  };

  $scope.next = function () {
    if ($scope.page > $scope.total - 1) {
      return;
    }
    $scope.current = $scope.files[$scope.page++];
    $scope.comparingFiles = [$scope.current];
  };

  $scope.showPrev = function () {
    return $scope.page > 1;
  };

  $scope.showNext = function () {
    return $scope.page < $scope.total;
  };

  var go = function (index) {
    $scope.page = index + 1;
    $scope.current = $scope.files[index];
    $scope.comparingFiles = [$scope.current];
  };


  /**
   * Sidebar selection
   */

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


  /**
   * Comparing files
   * This should totally go to its own directive :(
   */

  $scope.showCompareSelector = false;
  $scope.comparingFiles = [$scope.current];
  var comparingFilesLimit = 3;

  $scope.openCompareSelector = function () {
    $scope.showCompareSelector = true;
  };

  $scope.closeCompareSelector = function () {
    $scope.showCompareSelector = false;
  };

  $scope.compare = function () {
    // Update list with files that have been selected
    $scope.comparingFiles = $scope.files.filter(function (file) {
      return file.comparing === true;
    });
    $scope.current = $scope.comparingFiles[0];
    $scope.closeCompareSelector();
  };

  var selectForCompare = function (file) {
    if (file.comparing) {
      file.comparing = false;
    } else if ($scope.comparingFiles.length < comparingFilesLimit) {
      file.comparing = true;
    }
  };

  $scope.onCompareItemClick = function (file, index, $event) {
    if ($event.shiftKey) {
      selectForCompare(file, index);
    } else {
      go(index);
      $scope.closeCompareSelector();
    }
  };

  $scope.uncompare = function (file, index) {
    if ($scope.comparingFiles.length > 1) {
      file.comparing = false;
      var removed = $scope.comparingFiles.splice(index, 1);
      if ($scope.current === removed[0]) {
        $scope.current = $scope.comparingFiles[0];
      }
    } else {
      $modalInstance.dismiss('cancel');
    }
  };


  /**
   * Shortcuts
   */

  hotkeys.add({
    combo: 'left',
    callback: function () {
      if ($scope.showCompareSelector) {
        return;
      }
      $scope.prev();
    }
  });

  hotkeys.add({
    combo: 'right',
    callback: function () {
      if ($scope.showCompareSelector) {
        return;
      }
      $scope.next();
    }
  });

  hotkeys.add({
    combo: 'c',
    callback: function () {
      if ($scope.showCompareSelector) {
        $scope.closeCompareSelector();
      } else {
        $scope.openCompareSelector();
      }
    }
  });

  $scope.$on('$destroy', function () {
    hotkeys.del('left');
    hotkeys.del('right');
    hotkeys.del('c');
  });

};
