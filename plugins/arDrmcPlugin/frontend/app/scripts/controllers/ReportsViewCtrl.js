'use strict';

module.exports = function ($scope, $stateParams, ReportsService) {

  ReportsService.reportsViewData().then(function (data) {
    $scope.viewData = data;
  });

  ReportsService.getData($stateParams.type).then(function (response) {
    $scope.reportId = $stateParams.id;

    if ($stateParams.type === 'high_level_ingest') {
      $scope.highLevelIngest = response.data;
      $scope.reportMeta = {
        title: 'High Level Ingest',
        category: 'Activity Report'
      };
    }
  });

};
