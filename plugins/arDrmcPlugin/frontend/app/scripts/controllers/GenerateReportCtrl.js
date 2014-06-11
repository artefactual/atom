'use strict';

module.exports = function ($state, $scope, $modalInstance, $filter) {

  $scope.reportTypes = [
    {
      'name': 'High-level ingest report (activity)',
      'type': 'high_level_ingest'
    },
    {
      'name': 'Granular ingest report (activity)',
      'type': 'granular_ingest'
    },
    {
      'name': 'General download report (activity)',
      'type': 'general_download'
    },
    {
      'name': 'Amount downloaded report (activity)',
      'type': 'amount_downloaded'
    },
    {
      'name': 'Full fixity report (fixity)',
      'type': 'fixity'
    },
    {
      'name': 'Fixity error report (fixity)',
      'type': 'fixity_error'
    },
    {
      'name': 'Video characteristics report (characteristic)',
      'type': 'video_characteristics'
    },
    {
      'name': 'Component-level report (characteristic)',
      'type': 'component_level'
    }
  ];

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
    // Format $stateParams object to send over url to view page
    var to = $filter('date')($scope.criteria.range.to, 'yyyy-MM-dd');
    var from = $filter('date')($scope.criteria.range.from, 'yyyy-MM-dd');
    $modalInstance.close();
    $state.go('main.reports.preview', { type: $scope.criteria.type, to: to, from: from });
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
