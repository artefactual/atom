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

      // load parent AIP details, if necessary
      if ($scope.aip.parent != '') {
        AIPService.getAIPs({ uuid: $scope.aip.parent })
          .success(function (data) {
            $scope.aip.parent = data.aips.results[0];
          });
      }

      // load top-level parent AIP details, if necessary
      if ($scope.aip.partof != '') {
        AIPService.getAIPs({ uuid: $scope.aip.partof })
          .success(function (data) {
            $scope.aip.partof = data.aips.results[0];
          });
      }
    });
};
