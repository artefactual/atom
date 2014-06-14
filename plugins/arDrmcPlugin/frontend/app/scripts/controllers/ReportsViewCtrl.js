'use strict';

module.exports = function ($scope, $modal, $state, $stateParams, ReportsService, SETTINGS) {

  // Assign to $scope.downloadCsvLink the corresponding href attribute to
  // download the report in CSV from the API
  var getDownloadCsvLink = function () {
    if (angular.isUndefined($scope.reportParams.type)) {
      return;
    }
    $scope.downloadCsvLink = SETTINGS.frontendPath + 'api/reportcsv?type=' + $scope.reportParams.type;
    if (angular.isDefined($scope.reportParams.from)) {
      $scope.downloadCsvLink = $scope.downloadCsvLink + '&from=' + $scope.reportParams.from;
    }
    if (angular.isDefined($scope.reportParams.to)) {
      $scope.downloadCsvLink = $scope.downloadCsvLink + '&to=' + $scope.reportParams.to;
    }
  };

  // Fetch results from the server based in the given parameters
  var getReportResults = function () {
    ReportsService.getReportResults($scope.reportParams).then(function (response) {
      $scope.include = SETTINGS.viewsPath + '/partials/report_' + $scope.reportParams.type + '.html';
      $scope.reportData = response.data;
    });
  };

  // Obtain the report parameters. We may have to load them from the server if
  // this is a saved report, or from $stateParams if the report has not been
  // saved yet
  var getReportData = function () {

    // This object is going to hold the parameters of the report
    $scope.reportParams = {};

    if ($scope.savedReport) {

      // Load name, description and params from saved report
      ReportsService.getReportBySlug($stateParams.slug).then(function (response) {
        var data = response.data;

        // Report properties
        if (angular.isDefined(data.name)) {
          $scope.reportName = data.name;
        }
        if (angular.isDefined(data.description)) {
          $scope.reportDescription = data.description;
        }
        if (angular.isDefined(data.user_name)) {
          $scope.reportDescription = data.user_name;
        }
        if (angular.isDefined(data.created_at)) {
          $scope.reportDate = data.created_at;
        }

        // Report parameters
        if (angular.isDefined(data.type)) {
          $scope.reportParams.type = data.type;
        }
        if (angular.isDefined(data.range)) {
          if (angular.isDefined(data.range.from)) {
            $scope.reportParams.from = data.range.from;
          }
          if (angular.isDefined(data.range.to)) {
            $scope.reportParams.to = data.range.to;
          }
        }

        getDownloadCsvLink();
        getReportResults();
        $scope.title = ReportsService.getTitleByType($scope.reportParams.type);

      });

    } else if (angular.isDefined($stateParams.type)) {

      $scope.reportParams.type = $stateParams.type;
      if ($stateParams.from !== null) {
        $scope.reportParams.from = new Date($stateParams.from).getTime();
      }
      if ($stateParams.to !== null) {
        $scope.reportParams.to = new Date($stateParams.to).getTime();
      }

      getDownloadCsvLink();
      getReportResults();
      $scope.title = ReportsService.getTitleByType($scope.reportParams.type);

    }
  };

  // Open modal
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
    }).result.then(function () {
      $state.go('main.search.entity', { 'entity': 'reports' });
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

  // Is this a saved report?
  $scope.savedReport = angular.isDefined($stateParams.slug);

  getReportData();

};
