'use strict';

angular.module('momaApp.controllers')
  .controller('WorksViewCtrl', function ($scope, $stateParams) {

    console.log($stateParams);
    $scope.id = $stateParams.id;

  });
