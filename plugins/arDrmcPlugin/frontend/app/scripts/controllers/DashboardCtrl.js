'use strict';

module.exports = function ($scope, $q, StatisticsService) {

  $scope.radioModel = 'size';
  /**
   * Run queries parallely
   * Alphabetized by name
   */

  var pull = function () {
    //var artworkByMonthSummary = StatisticsService.getArtworkByMonthSummary();
    var downloadActivity = StatisticsService.getDownloadActivity();
    var ingestionActivity = StatisticsService.getIngestionActivity();
    var ingestionSummary = StatisticsService.getIngestionSummary();
    var storageCodec = StatisticsService.getRunningTotalByCodec();
    var storageFormats = StatisticsService.getRunningTotalByFormats();

    $scope.responses = {};
    $q.all([downloadActivity, ingestionActivity, ingestionSummary, storageCodec, storageFormats]).then(function (responses) {
      $scope.responses.downloadActivity = responses[0].data.results;
      $scope.responses.ingestionActivity = responses[1].data.results;
      $scope.responses.ingestionSummary = responses[2].data.results;
      $scope.responses.storageCodec = responses[3].data.results;
      $scope.responses.storageFormats = responses[4].data.results;
    });

  };
  pull();
  console.log('scope in ctrl at end', $scope);
  // TODO: DashboardIngestionCtrl and DashboardRecentActivityCtrl... unused now!

};
