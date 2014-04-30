'use strict';

module.exports = function ($scope) {

  // Support Reports overview toggling
  $scope.showOverview = true;
  $scope.toggleOverview = function () {
    $scope.showOverview = !$scope.showOverview;
  };
};
