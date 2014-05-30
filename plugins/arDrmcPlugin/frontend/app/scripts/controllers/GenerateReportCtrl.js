'use strict';

module.exports = function ($scope, $modalInstance) {

  // Create object to define
  $scope.report = {};
  $scope.criteria = {};
  $scope.criteria.range = {};

  // Checks for valid dates and generate report
  $scope.generateReport = function () {
    if ($scope.criteria.range.from !== undefined && $scope.criteria.range.from !== undefined) {
      if($scope.criteria.range.from < $scope.criteria.range.to) {
        $scope.datesInvalid = false;
        $modalInstance.close('with result');
      } else {
        $scope.datesInvalid = true;
      }
    } else {
      $scope.datesUndefined = true;
    }
  };

  // Reset all fields to empty
  $scope.resetFields = function () {
    $scope.report = {};
    $scope.criteria.range = {};
  };

  $scope.reportTypes = [
  // For now, number are arbitrary
    {
      'id': 500,
      'type_id': 20,
      'type_name': 'activity',
      'name': 'High-level ingest report'
    },
    {
      'id': 501,
      'type_id': 20,
      'type_name': 'activity',
      'name': 'Granular ingest report'
    },
    {
      'id': 502,
      'type_id': 20,
      'type_name': 'activity',
      'name': 'General download report'
    },
    {
      'id': 503,
      'type_id': 20,
      'type_name': 'activity',
      'name': 'Amount downloaded report'
    },
    {
      'id': 504,
      'type_id': 21,
      'type_name': 'fixity',
      'name': 'Full fixity report'
    },
    {
      'id': 505,
      'type_id': 21,
      'type_name': 'fixity',
      'name': 'Fixity error report'
    },
    {
      'id': 506,
      'type_id': 22,
      'type_name': 'characteristic',
      'name': 'Component-level report'
    },
    {
      'id': 507,
      'type_id': 22,
      'type_name': 'characteristic',
      'name': 'File-level report'
    }
  ];

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

};
