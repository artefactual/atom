'use strict';

module.exports = function (
  $scope,
  $state,
  $stateParams,
  $modal,
  $timeout,
  SETTINGS,
  InformationObjectService,
  ModalDigitalObjectViewerService,
  ModalDownloadService) {

  // TODO: Use https://github.com/angular-ui/ui-router/wiki#resolve
  InformationObjectService.getArtworkRecordWithTms($stateParams.id).then(function (data) {
    $scope.work = data;
    $scope.title = getTitle(data);
    getStatus();
  }, function (response) {
    throw response;
  });

  function getTitle (work) {
    var title = work.title;
    if (work.hasOwnProperty('tms') && work.tms.hasOwnProperty('year')) {
      title += ' (' + work.tms.year + ')';
    }
    if (work.hasOwnProperty('tms') && work.tms.hasOwnProperty('artist')) {
      title += ', ' + work.tms.artist;
    }
    return title;
  }

  var timer;
  $scope.updating = false;

  // Checks if the artwork is up to date with the TMS and if it's being updated.
  // If the artwork isn't updated it calls a Gearman Worker that fetch the TMS
  // and updates all the tree. If the job is accepted it checks the status after 2 secs.
  // If the artwork is being updated the status is checked every 2 secs, and when
  // the job finish the page is reloaded.
  function getStatus () {
    InformationObjectService.getArtworkStatus($stateParams.id).then(function (data) {
      if (!data.hasOwnProperty('updating')) {
        console.log('Couldn\'t check if the Artwork is being updated');
      } else if (data.updating) {
        $scope.updating = true;
        timer = $timeout(getStatus, 2000);
        return;
      } else {
        if ($scope.updating) {
          $scope.updating = false;
          // Reload page
          // TODO: reload only TMS metadata and context browser
          $state.go('main.works.view', { id: $stateParams.id }, { reload: true });
        }
      }

      if (!data.hasOwnProperty('updated')) {
        console.log('Couldn\'t check if the Artwork is up to date');
      } else if (!data.updated) {
        // Call worker
        InformationObjectService.updateArtworkTms($stateParams.id).then(function (response) {
          if (!response.hasOwnProperty('status') || response.status !== 202) {
            console.log('Couldn\'t update the Artwork');
          } else {
            $scope.updating = true;
            timer = $timeout(getStatus, 2000);
          }
        });
      } else {
        console.log('Artwork is up to date');
      }
    });
  }

  // A list of digital objects. This is shared within the context browser
  // directive (two-way binding);
  $scope.files = [];

  $scope.selectNode = function () {
    InformationObjectService.getAips($stateParams.id).then(function (data) {
      $scope.aggregation = data.overview;
    });
  };

  // TODO: downloadFile, downloadAip and openDigitalObjectModal is used both in
  // WorkViewCtrl and TechnologyRecordViewctrl, inside aip-overview.html. Create
  // a directive that can be shared instead of having duplicated functionality.

  $scope.downloadFile = function (file) {
    ModalDownloadService.downloadFile(
      file.aip_name,
      file.aip_uuid,
      file.id,
      file.original_relative_path_within_aip
    );
  };

  $scope.downloadAip = function (file) {
    ModalDownloadService.downloadAip(file.aip_name, file.aip_uuid);
  };

  $scope.openDigitalObjectModal = function (files, index) {
    ModalDigitalObjectViewerService.open(files, index);
  };

  $scope.$on('$destroy', function () {
    $timeout.cancel(timer);
  });

};
