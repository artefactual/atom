'use strict';

module.exports = function ($scope, $q, StatisticsService) {

  /**
   * Run queries parallely
   */
  var pull = function () {
    var downloadActivity = StatisticsService.getDownloadActivity();
    var ingestionActivity = StatisticsService.getIngestionActivity();

    $q.all([downloadActivity, ingestionActivity]).then(function (responses) {
      $scope.downloadActivity = responses[0].data.results;
      $scope.ingestionActivity = responses[1].data.results;
    });
  };

  pull();

  // TODO: DashboardIngestionCtrl and DashboardRecentActivityCtrl... unused now!

};
