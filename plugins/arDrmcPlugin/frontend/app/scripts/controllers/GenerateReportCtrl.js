'use strict';

module.exports = function ($scope, $modalInstance) {

  $scope.reportTypes = [
    {
      'name': 'High-level ingest report',
      'type': 'high_level_ingest'
    },
    {
      'name': 'Granular ingest report',
      'type': 'granular_ingest'
    },
    {
      'name': 'General download report',
      'type': 'general_download'
    },
    {
      'name': 'Amount downloaded report',
      'type': 'amount_downloaded'
    },
    {
      'name': 'Full fixity report',
      'type': 'fixity'
    },
    {
      'name': 'Fixity error report',
      'type': 'fixity_error'
    },
    {
      'name': 'Component-level report',
      'type': 'component_level'
    },
    {
      'name': 'File-level report',
      'type': 'file_level'
    }
  ];

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.modalContainer = {};
  $scope.modalContainer.dateRange = 'all';

  // Create object to define and set defaults
  $scope.criteria = {};

  // Checks for valid dates and generate report (with or without save)
  $scope.submit = function () {
    console.log($scope.criteria);
    if ($scope.modalContainer.dateRange === 'all') {
      delete $scope.criteria.range;
    }
  };

  // Reset all fields to empty
  $scope.reset = function () {
    $scope.criteria.reportType = {};
    $scope.criteria.range = 'all';
    $scope.criteria.savedName = {};
    $scope.criteria.savedDescription = {};
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

};
