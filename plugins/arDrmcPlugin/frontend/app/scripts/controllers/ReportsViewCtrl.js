'use strict';

module.exports = function ($scope, ReportsService) {

  ReportsService.reportsViewData().then(function (data) {
    $scope.viewData = data;
  });

};
