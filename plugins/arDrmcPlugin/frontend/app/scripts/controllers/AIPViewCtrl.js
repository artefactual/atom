'use strict';

module.exports = function ($scope, ATOM_CONFIG, $stateParams, AIPService) {

  // console.dir($scope);
  $scope.id = $stateParams.id;

  // This passes parameter for specific
  //  aip id via its uuid

  AIPService.getAIPs({ uuid: $scope.id })
    .success(function (data) {
      $scope.data = data;
      console.log(data);
      $scope.aip = data.aips.results[0];
      console.log($scope.aip);
    });

};
