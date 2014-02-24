'use strict';

module.exports = function ($scope, $stateParams, InformationObjectService) {

  InformationObjectService.getWork($stateParams.id)
    .then(function (result) {
      $scope.work = result;
    }, function (reason) {
      console.log('Failed', reason);
    }, function (update) {
      console.log('Notification', update);
    });

};
