'use strict';

module.exports = function ($scope, $stateParams, InformationObjectService) {

  InformationObjectService.getWork($stateParams.id)
    .then(function (response) {
      $scope.work = response.data;
      throw $scope.work;
    }, function (reason) {
      throw reason;
    });

};
