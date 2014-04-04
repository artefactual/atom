'use strict';

module.exports = function ($scope, $q, StatisticsService) {
  /**
   * Run queries parallely
   */
  var pull = function () {
    var downloadActivity = StatisticsService.getDownloadActivity();
    var ingestionActivity = StatisticsService.getIngestionActivity();

    $q.all([downloadActivity, ingestionActivity]).then(function (results) {
      $scope.downloadActivity = results[0].data;
      $scope.ingestionActivity = results[0].data;
    });
  };

  pull();
};
