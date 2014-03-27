
'use strict';

module.exports = function ($scope, $modalInstance) {

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
};
