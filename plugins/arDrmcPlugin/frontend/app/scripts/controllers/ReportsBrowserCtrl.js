'use strict';

module.exports = function ($scope, $modal, $stateParams, ReportsService, SETTINGS) {

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
      pull();
    });
  };

  var pull = function () {
    ReportsService.getBrowse().then(function (response) {
      $scope.browseData = response.data;

      var activityReportCount = $scope.browseData.overview.counts['High-level ingest reports'] + $scope.browseData.overview.counts['Granular ingest reports'] + $scope.browseData.overview.counts['General download reports'] + $scope.browseData.overview.counts['Amount downloaded reports'];
      if (angular.isDefined(activityReportCount) && !isNaN(activityReportCount)) {
        $scope.activityReportCount = activityReportCount;
      }

      var fixityReportCount = $scope.browseData.overview.counts['Fixity error reports'] + $scope.browseData.overview.counts['Fixity reports'];
      if (angular.isDefined(fixityReportCount) && !isNaN(fixityReportCount)) {
        $scope.fixityReportCount = fixityReportCount;
      }

      var characteristicReportCount = $scope.browseData.overview.counts['File level reports'] + $scope.browseData.overview.counts['Component level reports'];
      if (angular.isDefined(characteristicReportCount) && !isNaN(characteristicReportCount)) {
        $scope.characteristicReportCount = characteristicReportCount;
      }
    });
  };

  $scope.selectedReports = [];

  // Toggle selected report
  $scope.toggleSelection = function (id) {
    console.log(id);
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
      throw 'Error deleting search ' + id;
    });
  };

  pull();

};
