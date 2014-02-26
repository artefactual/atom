'use strict';

module.exports = function ($scope, $stateParams, AIPService) {

  AIPService.getAIP($stateParams.uuid)
    .success(function (data) {
      $scope.aip = data;
    });

};
