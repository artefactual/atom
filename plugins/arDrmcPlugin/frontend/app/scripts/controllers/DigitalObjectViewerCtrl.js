'use strict';

module.exports = function ($scope, $q, $modal, $stateParams, $modalInstance, hotkeys, InformationObjectService, ModalDigitalObjectViewerService, files, index) {

  // Reference provided list of files from the model
  $scope.files = files;

  // Set page and current file based in the index parameter, which may be not
  // defined when the user is not visualizing a file in particular
  index = index === null ? 0 : index;
  $scope.current = $scope.files[index];
  $scope.page = index + 1;
  $scope.total = $scope.files.length;

  // Get class by media type
  $scope.getMediaType = function () {
    // file.class = ModalDigitalObjectViewerService.mediaTypes[file.media_type_id].class;
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
   * Sidebar
   */

  // Defaults for the sidebar.
  // Notice that ng-repeat will inherit this and then rewrite
  $scope.showAipsArea = true;
  $scope.showFitsArea = false;
  $scope.showMediaArea = false;


  /**
   * Pager and selection
   */

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

  $scope.showPrev = function () {
    return $scope.page > 1;
  };

  $scope.showNext = function () {
    return $scope.page < $scope.total;
  };

  var go = function (index) {
    $scope.page = index + 1;
    $scope.current = $scope.files[index];
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

  $scope.getSidebarFiles = function () {
    return [$scope.current];
  };


  /**
   * Comparing files
   */

  $scope.showCompareSelector = false;
  $scope.comparingFiles = [$scope.current];
  var comparingFilesLimit = 3;

  var selectForCompare = function (file, index) {
    if (file.comparing) {
      file.comparing = false;
    } else if ($scope.comparingFiles.length < comparingFilesLimit) {
      file.comparing = true;
    }
    console.log(index);
  };

  $scope.onCompareItemClick = function (file, index, $event) {
    if ($event.shiftKey) {
      selectForCompare(file, index);
    } else {
      go(index);
      $scope.closeCompareSelector();
    }
  };

  $scope.openCompareSelector = function () {
    $scope.showCompareSelector = !$scope.showCompareSelector;
  };

  $scope.closeCompareSelector = function () {
    $scope.showCompareSelector = false;
  };

  $scope.compare = function () {
    $scope.comparingFiles = [];
    for (var i in $scope.files) {
      var f = $scope.files[i];
      if (f.comparing) {
        $scope.comparingFiles.push(f);
      }
    }
    $scope.closeCompareSelector();
  };

  $scope.uncompare = function (file, index) {
    if ($scope.comparingFiles.length > 1) {
      $scope.comparingFiles.splice(index, 1);
      file.comparing = false;
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
