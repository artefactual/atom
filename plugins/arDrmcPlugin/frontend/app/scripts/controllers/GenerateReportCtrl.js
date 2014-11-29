(function () {

  'use strict';

  angular.module('drmc.controllers').controller('GenerateReportCtrl', function ($state, $scope, $modalInstance, ReportsService) {

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
      // Extract properties from the range object
      // TODO: update directive so nesting is not required?
      if (angular.isDefined($scope.criteria.range)) {
        $scope.criteria.from = $scope.criteria.range.from;
        $scope.criteria.to = $scope.criteria.range.to;
        delete $scope.criteria.range;
      }
      // Close modal and forward
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

  });

})();
