'use strict';

module.exports = function ($scope, $stateParams, $modal, SETTINGS, InformationObjectService, ModalDigitalObjectViewerService) {

  // TODO: Use https://github.com/angular-ui/ui-router/wiki#resolve
  InformationObjectService.getArtworkRecordWithTms($stateParams.id).then(function (data) {
    $scope.work = data;
    $scope.title = getTitle(data);
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

  $scope.openViewer = function () {
    ModalDigitalObjectViewerService.open();
  };

  $scope.selectNode = function () {
    InformationObjectService.getAips($stateParams.id).then(function (data) {
      $scope.aggregation = data.overview;
    });
  };
};
