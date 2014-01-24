'use strict';

module.exports = function ($scope, $stateParams, AIPService) {

  $scope.id = $stateParams.id;

  AIPService.getAIPs()
    .success(function (data) {
      $scope.data = data;
    });

  // Support overview toggling
  $scope.showOverview = true;
  $scope.toggleOverview = function () {
    $scope.showOverview = !$scope.showOverview;
  };

};
