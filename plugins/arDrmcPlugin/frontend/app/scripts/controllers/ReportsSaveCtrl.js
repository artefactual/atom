'use strict';

module.exports = function ($scope, $state, $modalInstance, ReportsService, data) {

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.modalContainer = {};

  // Create object to define and set defaults
  $scope.criteria = data;

  var copy = {};
  angular.copy($scope.criteria, copy);

  // Checks for valid dates and generate report (with or without save)
  $scope.submit = function () {
    if ($scope.modalContainer.form.$invalid) {
      return;
    }
    if ($scope.modalContainer.dateRange === 'all') {
      delete $scope.criteria.range;
    }
    // Access to the server
    ReportsService.saveReport($scope.criteria).then(function (data) {
      $scope.id = data.id;
    });
    // Close
    $modalInstance.close();
    $state.go('main.search.entity', { 'entity': 'reports' });
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
};
