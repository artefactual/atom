'use strict';

module.exports = function ($scope, $modal, SETTINGS, ReportsService) {

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
    ReportsService.reportsBrowseData().then(function (data) {
      $scope.reportsData = data;
    });
  };

  pull();

};
