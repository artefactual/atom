'use strict';

module.exports = function ($scope, $modalInstance, AIPService) {

  // Save button
  $scope.reclassify = function () {
    // Update .class after selection
    $scope.aip.class = $scope.classifications[$scope.aip.class_id];
    // Post the change
    AIPService.reclassifyAIP($scope.aip.id, $scope.aip.class)
      // If the update succeeded, update AIPBrowserCtrl overview (pull) and close
      .success(function () {
        $scope.pull();
        $modalInstance.close($scope.aip.class);
      }).error(function () {
        $modalInstance.dismiss('Your new classification could not be assigned');
      });
  };

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

};
