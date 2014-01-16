'use strict';

angular.module('momaApp')
  .controller('AIPsViewCtrl', function ($scope, $stateParams) {

    $scope.aipId = $stateParams.aipId;

});
