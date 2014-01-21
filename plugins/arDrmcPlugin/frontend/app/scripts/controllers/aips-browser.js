'use strict';

angular.module('momaApp.controllers')
  .controller('AIPsBrowserCtrl', function ($scope, $stateParams, AIPService) {

    $scope.id = $stateParams.id;

    AIPService.getAIPs()
      .success(function (data, status) {
        $scope.data = data;
      });

    // Support overview toggling
    $scope.showOverview = true;
    $scope.toggleOverview = function() {
      $scope.showOverview = !$scope.showOverview;
    };

  });
