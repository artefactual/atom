'use strict';

module.exports = function ($scope, $modalInstance) {

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  // Create object to define
  $scope.report = {};

  // Set a default report
  $scope.report.type_id = 500;
  $scope.generateReport = function () {
    console.log($scope.report.type_id);
    $modalInstance.close('with result');
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

  console.log($scope.reportTypes);

};
