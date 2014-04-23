'use strict';

module.exports = function ($scope, ModalDigitalObjectViewerService) {

  $scope.openViewer = function (file) {
    ModalDigitalObjectViewerService.open(file);
  };

};
