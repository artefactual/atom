'use strict';

module.exports = function ($scope, $modal, $state, $stateParams, ReportsService, SETTINGS) {

  var pull = function () {
    ReportsService.getBrowse().then(function (response) {
      $scope.browseData = response.data;
    });
  };

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
    }).result.then(function () {
      //TODO: This doesn't update page
      pull();
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
    $scope.selectedReports = [];
  };

  var _delete = function (id) {
    ReportsService.deleteReport(id).then(function () {
      pull();
    }, function () {
      throw 'Error deleting report ' + id;
    });
  };

  pull();

};
