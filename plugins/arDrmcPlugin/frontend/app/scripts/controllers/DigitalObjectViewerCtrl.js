'use strict';

module.exports = function ($scope, $modal, $modalInstance) {

  $scope.digitalObjects = {
    1: {
      id: 1,
      demo_title: 'playdead_realtime.tif',
      aip_details: {
        id: 123,
        name: 'Play Dead; Real Time',
        uuid: '74b4848e-6d1c-488b-b7e6-72abaf7b09b7',
        size: '34534454',
        no_objects: '4',
        classification: 'Supporting Documentation',
        description: 'Voluptatem fugiat enim omnis eaque architecto fugiat quod iusto dolorum ut. Eaque totam nam laudantium nemo corrupti. Ea vitae quia magni nihil saepe.'
      },
      fits_output: {
        complete_name: '/volumes/drmc/storage/digitized_images/active/Exhibition_stil-g38j12349-sdfa-d34f5',
        uuid: 'bdc0a30df517e850',
        mime_type: 'image/TIFF',
        format: 'TIF', // use pronom id?
        file_size: '35234526374',
        dimensions: '1000x3000px',
        compression: 'Uncompressed',
        date_time: '2012-09-14 19:42:03 EST'
      },
      digital_object: { // FIXME THIS WAS img BEFORE
        media_type: 'image',
        width: '3000px',
        xres: '2400000/10000',
        height: '480px',
        yres: '2400000/1000000000', // use pronom id?
        res: '4:3',
        make: 'Sinar',
        model: '54H'
      }
    },
    2: {
      id: 2,
      demo_title: 'Digital object B',
      digital_object: { // FIXME THIS WAS img BEFORE
        media_type: 'image',
        width: '3000px',
        xres: '2400000/10000',
        height: '480px',
        yres: '2400000/1000000000', // use pronom id?
        res: '4:3',
        make: 'Sinar',
        model: '54H'
      }
    },
    3: {
      id: 3,
      demo_title: 'Digital object C',
      digital_object: { // FIXME THIS WAS img BEFORE
        media_type: 'video',
        width: '3000px',
        xres: '2400000/10000',
        height: '480px',
        yres: '2400000/1000000000', // use pronom id?
        res: '4:3',
        make: 'Sinar',
        model: '54H'
      }
    },
  };

  var keys = Object.keys($scope.digitalObjects);
  $scope.page = 1;
  $scope.total = keys.length;
  $scope.current = $scope.digitalObjects[keys[$scope.page - 1]];

  // Shortcuts REMOVE THIS
  $scope.digObj1Aip = $scope.digitalObjects[1].aip_details;
  $scope.digObj1Fits = $scope.digitalObjects[1].fits_output;
  $scope.digObj1Img = $scope.digitalObjects[1].digital_object;

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  $scope.aipsToggle = true;
  $scope.fitsToggle = true;
  $scope.mediaDetailsToggle = true;

  $scope.prev = function () {
    if ($scope.page < 2) {
      return;
    }
    $scope.current = $scope.digitalObjects[keys[$scope.page--]];
  };

  $scope.next = function () {
    if ($scope.page > keys.length - 1) {
      return;
    }
    $scope.current = $scope.digitalObjects[keys[$scope.page++]];
  };

  $scope.showPrev = function () {
    return $scope.page > 1;
  };

  $scope.showNext = function () {
    return $scope.page < keys.length;
  };

};
