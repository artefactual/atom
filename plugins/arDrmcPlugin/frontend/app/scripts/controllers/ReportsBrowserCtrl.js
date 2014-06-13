'use strict';

module.exports = function ($scope, $modal, $state, ReportsService, SETTINGS) {

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
    for (var key in $scope.selectedReports) {
      _delete($scope.selectedReports[key]);
    }
    $scope.$parent.updateResults();
    $scope.selectedReports = [];
  };

  var _delete = function (id) {
    ReportsService.deleteReport(id).then(function () {
    }, function () {
      throw 'Error deleting report ' + id;
    });
  };

};
