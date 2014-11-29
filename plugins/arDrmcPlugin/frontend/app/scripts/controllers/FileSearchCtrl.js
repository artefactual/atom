(function () {

  'use strict';

  angular.module('drmc.controllers').controller('FileSearchCtrl', function ($scope, ModalDigitalObjectViewerService) {

    $scope.openViewer = function (file) {
      ModalDigitalObjectViewerService.open(file);
    };

  });

})();
