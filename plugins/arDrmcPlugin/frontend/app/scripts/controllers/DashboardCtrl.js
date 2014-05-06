'use strict';

module.exports = function ($scope, $q, StatisticsService) {

  $scope.dashboardGraphs = {};
  $scope.dashboardGraphs.radioModel = 'number';
  /**
   * Run queries parallely
   * Alphabetized by name
   */

  var pull = function () {
    //var artworkByMonthSummary = StatisticsService.getArtworkByMonthSummary();
    var downloadActivity = StatisticsService.getDownloadActivity();
    var ingestionActivity = StatisticsService.getIngestionActivity();
    //var ingestionSummary = StatisticsService.getIngestionSummary();
    var storageCodec = StatisticsService.getRunningTotalByFormats();

    $scope.responses = {};
    $q.all([downloadActivity, ingestionActivity, storageCodec]).then(function (responses) {
      $scope.responses.downloadActivity = responses[0].data.results;
      $scope.responses.ingestionActivity = responses[1].data.results;
      $scope.responses.storageCodec = responses[2].data.results;
    });

  };
  pull();
  // TODO: DashboardIngestionCtrl and DashboardRecentActivityCtrl... unused now!

};
