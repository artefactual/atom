'use strict';

module.exports = function ($scope, $stateParams, $modal, SETTINGS, InformationObjectService, ModalDigitalObjectViewerService, ModalDownloadService) {

  // TODO: Use https://github.com/angular-ui/ui-router/wiki#resolve
  InformationObjectService.getArtworkRecordWithTms($stateParams.id).then(function (data) {
    $scope.work = data;
    $scope.title = getTitle(data);
    // TODO: compare LastModifiedDate from TMS
    InformationObjectService.updateArtworkTms($stateParams.id).then(function (response) {
      // TODO: check response to see if it's updating and add timer to reload artwork
      console.log(response);
    });
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

};
