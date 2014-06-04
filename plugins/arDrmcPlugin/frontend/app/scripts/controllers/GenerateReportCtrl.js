'use strict';

module.exports = function ($scope, $modalInstance, ReportsService) {

  // Create object to define and set defaults
  $scope.criteria = {};
  $scope.criteria.range = 'all';

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

  // Checks for valid dates and generate report (with or without save)
  $scope.generateReport = function (criteria) {
    ReportsService.generateReport(criteria).success(function (response) {
      $modalInstance.close(response);
    });
  };

  // Reset all fields to empty
  $scope.resetFields = function () {
    $scope.criteria.reportType = {};
    $scope.criteria.range.value = {};
    $scope.radioOpt = 'all';
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

};
