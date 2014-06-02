'use strict';

module.exports = function ($scope, $modalInstance, ReportsService) {

  // Create object to define
  $scope.report = {};
  $scope.criteria = {};
  $scope.criteria.range = {};

  // Checks for valid dates and generate report
  $scope.generateReport = function (params) {
    ReportsService.generateReport(params).success(function (response) {
      $modalInstance.close(response);
    });
  };

  // Reset all fields to empty
  $scope.resetFields = function () {
    $scope.report = {};
    $scope.criteria.range = {};
  };

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

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

};
