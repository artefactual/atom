'use strict';

module.exports = function ($scope, $modal, $stateParams, ReportsService, SETTINGS) {

  var getGenerated = function () {
    ReportsService.getGenerated($stateParams.type).then(function (response) {
      $scope.include = SETTINGS.viewsPath + '/partials/' + $stateParams.type + '.html';
      $scope.reportData = response.data;
    });
  };

  getGenerated();

  $scope.openSaveReportModal = function () {
    $modal.open({
      templateUrl: SETTINGS.viewsPath + '/modals/save-report.html',
      backdrop: true,
      controller: 'ReportsSaveCtrl',
      windowClass: 'modal-large',
      resolve: {
        // Bring data about current generated report into modal
        data: function () {
          var s = $stateParams;
          return s;
        }
      }
    });
  };

  // Checks for valid dates and generate report (with or without save)
  $scope.submit = function () {
    if ($scope.modalContainer.form.$invalid) {
      return;
    }
    if ($scope.modalContainer.dateRange === 'all') {
      delete $scope.criteria.range;
    }
    // Access to the server
    ReportsService.saveReport($scope.criteria).then(function (data) {
      $scope.id = data.id;
    });
    // Close
    $modal.close();
  };

};
