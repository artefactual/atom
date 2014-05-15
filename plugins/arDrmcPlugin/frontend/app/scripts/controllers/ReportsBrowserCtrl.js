'use strict';

module.exports = function ($scope, $modal, SETTINGS, ReportsService) {

  $scope.openGenerateReportModal = function () {
    var modalConfig =  $modal.open ({
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

    modalConfig.result.then(function () {
    });
  };

  ReportsService.asyncReportData().then(function (response) {
    return response;
  }).then(function (asyncData) {
    $scope.asyncData = asyncData;
  });

  // Support Reports saved reports toggling
  $scope.showSavedReports = true;
  $scope.toggleSavedReports = function () {
    $scope.showSavedReports = !$scope.showSavedReports;
  };

    // Support Reports overview toggling
  $scope.showReportsOverview = true;
  $scope.toggleReportsOverview = function () {
    $scope.showReportsOverview = !$scope.showReportsOverview;
  };
};
