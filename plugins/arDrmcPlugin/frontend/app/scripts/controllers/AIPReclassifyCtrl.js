'use strict';

module.exports = function ($scope, $modalInstance, AIPService) {

  // Save button
  $scope.reclassify = function () {
    // Post the change
    AIPService.reclassifyAIP($scope.aip.uuid, $scope.aip.type.id)
      // If the update succeeded, update AIPBrowserCtrl overview (pull) and close
      .success(function () {
        $scope.search();
        $modalInstance.close($scope.aip.type.id);
      }).error(function () {
        $modalInstance.dismiss('Your new classification could not be assigned');
      });
  };

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

};
