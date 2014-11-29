(function () {

  'use strict';

  angular.module('drmc.controllers').controller('ReportsSaveCtrl', function ($scope, $state, $modalInstance, ReportsService, data) {

    // HACK: form scoping issue within modals, see
    // - http://stackoverflow.com/a/19931221/2628967
    // - https://github.com/angular-ui/bootstrap/issues/969
    $scope.modalContainer = {};

    // Create object to define and set defaults
    $scope.criteria = data;

    // Checks for valid dates and generate report (with or without save)
    $scope.submit = function () {
      if ($scope.modalContainer.form.$invalid) {
        return;
      }
      if ($scope.modalContainer.dateRange === 'all') {
        delete $scope.criteria.range;
      }
      // Save report
      ReportsService.saveReport($scope.criteria).then(function (response) {
        $modalInstance.close(response.data.id);
      }, function (response) {
        console.log('Error saving report', response);
        $modalInstance.dismiss('error');
      });
    };

    $scope.cancel = function () {
      $modalInstance.dismiss('cancel');
    };

  });

})();
