'use strict';

module.exports = function ($scope, $q, $modal, $state, ReportsService, SETTINGS) {

  // Default sorting options
  $scope.criteria.sort_direction = 'desc';
  $scope.criteria.sort = 'createdAt';

  $scope.openGenerateReportModal = function () {
    $modal.open({
      templateUrl: SETTINGS.viewsPath + '/modals/generate-report.html',
      backdrop: true,
      controller: 'GenerateReportCtrl',
      windowClass: 'modal-large',
      resolve: {
        data: function () {
          return $scope.data;
        }
      }
    });
  };

  // Store ids of searches with checkbox selected
  $scope.selectedReports = [];

  // Toggle selected report
  $scope.toggleSelection = function (id) {
    var index = $scope.selectedReports.indexOf(id);
    if (index > -1) {
      $scope.selectedReports.splice(index, 1);
    } else {
      $scope.selectedReports.push(id);
    }
  };

  $scope.delete = function () {
    var queries = [];
    for (var key in $scope.selectedReports) {
      var id = $scope.selectedReports[key];
      queries.push(ReportsService.deleteReport(id));
    }
    $q.all(queries).then(function () {
      $scope.$parent.updateResults();
      $scope.selectedReports = [];
    }, function (responses) {
      console.log('Error deleting saved reports', responses);
    });
  };

};
