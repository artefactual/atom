'use strict';

module.exports = function ($scope, SETTINGS, InformationObjectService) {

  InformationObjectService
    .getSupportingTechnologyRecords().then(function (response) {
      $scope.data = response.data;
      console.log($scope.data);
    });

};
