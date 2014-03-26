'use strict';

module.exports = function ($scope, $stateParams, $modal, SETTINGS, InformationObjectService) {

  // TODO: Use https://github.com/angular-ui/ui-router/wiki#resolve
  InformationObjectService.getArtworkRecordWithTms($stateParams.id).then(function (data) {
    $scope.work = data;
  }, function (response) {
    throw response;
  });

  // A list of digital objects. This is shared within the context browser
  // directive (two-way binding);
  $scope.files = [];

  $scope.openDigitalObjectModal = function () {
    var modalInstance = $modal.open({
      templateUrl: SETTINGS.viewsPath + '/modals/digital-object-viewer.html',
      backdrop: 'static', // Prevents close when clicking on backdrop
      controller: 'DigitalObjectViewerCtrl',
      scope: $scope, // TODO: isolate with .new()?
    });
    modalInstance.result.then(function (result) {
      console.log(result);
    });
  };

  $scope.selectNode = function () {
    InformationObjectService.getAips($stateParams.id).then(function (data) {
      $scope.aggregation = data.overview;
    });
  };
};
