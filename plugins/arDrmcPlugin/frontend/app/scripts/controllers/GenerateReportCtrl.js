'use strict';

module.exports = function ($state, $scope, $modalInstance, ReportsService) {

  $scope.reportTypes = ReportsService.types;

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.modalContainer = {};
  $scope.modalContainer.dateRange = 'all';

  // Create object to define and set defaults
  $scope.criteria = {};

  var copy = {};
  angular.copy($scope.criteria, copy);

  $scope.submit = function () {
    $modalInstance.close();
    $state.go('main.reports.view', $scope.criteria);
  };

  // Reset all fields to empty
  $scope.reset = function () {
    angular.copy(copy.criteria, $scope.criteria);
    $scope.modalContainer.dateRange = 'all';
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

};
