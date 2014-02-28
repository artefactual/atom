'use strict';

module.exports = function ($scope, $stateParams, $modal, SETTINGS, InformationObjectService) {

  InformationObjectService.getWork($stateParams.id)
    .then(function (response) {
      $scope.work = response.data;
    }, function (reason) {
      throw reason;
    });

  $scope.openDigitalObjectModal = function () {
    var modalInstance = $modal.open({
      templateUrl: SETTINGS.viewsPath + '/modals/digital-object-viewer.html',
      // Prevents close when clicking on backdrop
      backdrop: 'static',
      controller: 'DigitalObjectViewerCtrl',
      scope: $scope, // TODO: isolate with .new()?
    });
    modalInstance.result.then(function (result) {
      console.log(result);
    });
  };

};
