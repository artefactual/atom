'use strict';

module.exports = function ($scope, $stateParams, AIPService, ModalDigitalObjectViewerService) {

  AIPService.getAIP($stateParams.uuid)
    .success(function (data) {
      $scope.aip = data;
    });

  $scope.openViewer = function () {
    ModalDigitalObjectViewerService.open();
  };

};
