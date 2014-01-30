'use strict';

module.exports = function ($scope, InformationObjectService) {

  InformationObjectService.getWorks()
    .then(function (result) {

      $scope.works = result;

    }, function (reason) {

      console.log('Failed', reason);

    }, function (update) {

      console.log('Notification', update);

    });

};
