'use strict';

angular.module('momaApp.controllers')
  .controller('AIPsViewCtrl', function ($scope, $stateParams) {

    $scope.aipId = $stateParams.aipId;

});
