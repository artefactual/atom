'use strict';

module.exports = function ($state, $scope, $modalInstance, $filter, ReportsService) {

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

  // Use parameters from modal to generate a preview (not save) a report
  $scope.generate = function () {
    // Format dates
    if (typeof $scope.criteria.range !== 'undefined' && typeof $scope.criteria.range.to !== 'undefined') {
      $scope.criteria.to = $filter('date')($scope.criteria.range.to, 'yyyy-MM-dd');
    }
    if (typeof $scope.criteria.range !== 'undefined' && typeof $scope.criteria.range.from !== 'undefined') {
      $scope.criteria.from = $filter('date')($scope.criteria.range.from, 'yyyy-MM-dd');
    }
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
