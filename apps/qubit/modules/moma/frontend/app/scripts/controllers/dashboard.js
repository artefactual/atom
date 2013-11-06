'use strict';

angular.module('momaApp')
  .controller('DashboardCtrl', function ($scope, $http, atomGlobals) {

    $scope.atomGlobals = atomGlobals;

  });
