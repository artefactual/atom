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

  ReportsService.reportsBrowseData().then(function (data) {
    $scope.reportsData = data;
    console.log($scope);
  });

};
