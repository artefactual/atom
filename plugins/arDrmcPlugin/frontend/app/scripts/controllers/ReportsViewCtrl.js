'use strict';

module.exports = function ($scope, $stateParams, ReportsService, SETTINGS) {

  var getGenerated = function () {
    ReportsService.getGenerated($stateParams.type).then(function (response) {
      $scope.include = SETTINGS.viewsPath + '/partials/' + $stateParams.type + '.html';
      $scope.reportData = response.data;
    });
  };

  getGenerated();

};
