'use strict';

module.exports = function ($scope, SETTINGS, InformationObjectService) {

  InformationObjectService
    .getSupportingTechnologyRecords({
      only_root: true
    }).then(function (response) {
      $scope.data = response.data;
    });

};
