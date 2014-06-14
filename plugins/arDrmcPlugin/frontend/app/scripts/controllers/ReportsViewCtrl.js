'use strict';

module.exports = function ($scope, $modal, $stateParams, ReportsService, SETTINGS) {

  var getDownloadCsvLink = function () {
    if (typeof $scope.reportParams.type !== 'undefined') {
      $scope.downloadCsvLink = SETTINGS.frontendPath + 'api/reportcsv?type=' + $scope.reportParams.type;
      if (typeof $scope.reportParams.from !== 'undefined') {
        $scope.downloadCsvLink = $scope.downloadCsvLink + '&from=' + $scope.reportParams.from;
      }
      if (typeof $scope.reportParams.to !== 'undefined') {
        $scope.downloadCsvLink = $scope.downloadCsvLink + '&to=' + $scope.reportParams.to;
      }
    }
  };

  var getReportResults = function () {
    ReportsService.getReportResults($scope.reportParams).then(function (response) {
      $scope.include = SETTINGS.viewsPath + '/partials/report_' + $scope.reportParams.type + '.html';
      $scope.reportData = response.data;
    });
  };

  var getReportData = function () {
    // Store params in scope to show in overview
    $scope.reportParams = {};
    if (angular.isDefined($stateParams.slug)) {
      $scope.savedReport = true;
      // Load name. description and params from saved report
      ReportsService.getReportBySlug($stateParams.slug).then(function (response) {
        if (typeof response.data.name !== 'undefined') {
          $scope.reportName = response.data.name;
        }
        if (typeof response.data.description !== 'undefined') {
          $scope.reportDescription = response.data.description;
        }
        if (typeof response.data.user_name !== 'undefined') {
          $scope.reportUser = response.data.user_name;
        }
        if (typeof response.data.created_at !== 'undefined') {
          $scope.reportDate = response.data.created_at;
        }
        if (typeof response.data.type !== 'undefined') {
          $scope.reportParams.type = response.data.type;
        }
        if (typeof response.data.range !== 'undefined' && typeof response.data.range.to !== 'undefined') {
          $scope.reportParams.to = response.data.range.to;
        }
        if (typeof response.data.range !== 'undefined' && typeof response.data.range.from !== 'undefined') {
          $scope.reportParams.from = response.data.range.from;
        }
        getDownloadCsvLink();
        getReportResults();
      });
    } else if (angular.isDefined($stateParams.type)) {
      // Load params from stateParams
      $scope.reportParams.type = $stateParams.type;
      if ($stateParams.from !== null) {
        $scope.reportParams.from = new Date($stateParams.from).getTime();
      }
      if ($stateParams.to !== null) {
        $scope.reportParams.to = new Date($stateParams.to).getTime();
      }
      getDownloadCsvLink();
      getReportResults();
    }
  };

  // Store if it's a saved report to hide Save button, and link to download CSV
  $scope.savedReport = false;
  $scope.downloadCsvLink = '';

  getReportData();

  $scope.openSaveReportModal = function () {
    $modal.open({
      templateUrl: SETTINGS.viewsPath + '/modals/save-report.html',
      backdrop: true,
      controller: 'ReportsSaveCtrl',
      windowClass: 'modal-large',
      resolve: {
        // Bring data about current generated report into modal
        data: function () {
          return $scope.reportParams;
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
