'use strict';

module.exports = function ($scope, $stateParams, ReportsService, routeData) {

  ReportsService.reportsViewData().then(function (data) {
    $scope.viewData = data;
  });

  // Data comes to view before view is loaded through routing.js
  // TODO: Check against data report types
  // Maybe better to do in service once endpoint returns report type
  if (angular.isDefined(routeData)) {
    $scope.highLevelIngest = routeData.data;
    $scope.reportMeta = {
      title: 'High Level Ingest',
      category: 'Activity Report'
    };
  }

};
