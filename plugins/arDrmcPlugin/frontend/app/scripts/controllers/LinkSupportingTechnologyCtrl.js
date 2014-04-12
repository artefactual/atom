'use strict';

module.exports = function ($scope, $modalInstance,InformationObjectService) {
  $scope.resource = {};

  // temporary, until able to add supporting technologies
  $scope.criteria = {};
  InformationObjectService.getWorks($scope.criteria)
    .then(function (response) {
      $scope.resource.res = response.data;
      console.log('data from service', $scope.criteria, $scope.resource.res);
      return $scope.resource.res;
    });

  $scope.removeAll = function () {
    $scope.selectedData.length = 0;
  };

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
};
