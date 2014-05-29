'use strict';

module.exports = function ($scope, $modal, SETTINGS, ReportsService) {

  $scope.openGenerateReportModal = function () {
    $modal.open ({
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

  ReportsService.reportsBrowseData().then(function (data) {
    $scope.reportsData = data;
  });

};
