'use strict';

module.exports = function ($scope, $q, StatisticsService) {

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
    var artworkSizes = StatisticsService.getArtworkSizesByYearSummary();

    $scope.responses = {};
    $q.all([downloadActivity, ingestionActivity, ingestionSummary, storageCodec, storageFormats, artworkSizes]).then(function (responses) {
      $scope.responses.downloadActivity = responses[0].data.results;
      $scope.responses.ingestionActivity = responses[1].data.results;
      $scope.responses.ingestionSummary = responses[2].data.results;
      $scope.responses.storageCodec = responses[3].data.results;
      $scope.responses.storageFormats = responses[4].data.results;
      $scope.responses.artworkSizes = [{
        name: 'Average',
        color: 'steelblue',
        xProperty: 'year',
        yProperty: 'average',
        data: responses[5].data.results
      }];
    });

  };
  pull();
  // TODO: DashboardIngestionCtrl and DashboardRecentActivityCtrl... unused now!
};
