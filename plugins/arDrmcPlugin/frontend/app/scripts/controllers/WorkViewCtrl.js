'use strict';

module.exports = function ($scope, $stateParams, $modal, SETTINGS, InformationObjectService) {

  InformationObjectService.getWork($stateParams.id).then(function (response) {
    $scope.work = response.data;
    InformationObjectService.getTms($stateParams.id).then(function (response) {
      $scope.work.tms = response.data;
    });
  }, function (reason) {
    throw reason;
  });

  // A list of digital objects. This is shared within the context browser
  // directive (two-way binding);
  $scope.files = [];

  $scope.openDigitalObjectModal = function (files) {
    if (typeof files === 'undefined') {
    }
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

};
