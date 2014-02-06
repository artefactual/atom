'use strict';

module.exports = function ($scope, $stateParams, InformationObjectService) {

  InformationObjectService.getWorks()
    .then(function (result) {
      $scope.works = result;
    }, function (reason) {
      console.log('Failed', reason);
    }, function (update) {
      console.log('Notification', update);
    });

  InformationObjectService.getWork($stateParams.id)
    .then(function (result) {
      $scope.work = result;
    }, function (reason) {
      console.log('Failed', reason);
    }, function (update) {
      console.log('Notification', update);
    });

};
