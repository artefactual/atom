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
    var monthlyTotals = StatisticsService.getMonthlyTotalByCodec();

    $scope.responses = {};
    $q.all([downloadActivity, ingestionActivity, ingestionSummary, storageCodec, storageFormats, artworkSizes, monthlyTotals]).then(function (responses) {
      $scope.responses.downloadActivity = responses[0].data.results;
      $scope.responses.ingestionActivity = responses[1].data.results;
      $scope.responses.ingestionSummary = responses[2].data.results;
      $scope.responses.storageCodec = {
        accessKey: 'count',
        formatKey: 'media_type',
        data: responses[3].data.results
      };
      $scope.responses.storageFormats = {
        accessKey: 'total',
        formatKey: 'media_type',
        data: responses[4].data.results
      };
      $scope.responses.artworkSizes = [{
        name: 'Average',
        color: 'steelblue',
        xProperty: 'year',
        yProperty: 'average',
        data: responses[5].data.results
      }];
      $scope.responses.monthlyTotals = [{
        name: 'Month',
        color: 'hotpink',
        xProperty: 'month',
        yProperty: 'count',
        data: responses[6].data.results.collection
      }];
    });

  };
  pull();
  // TODO: DashboardIngestionCtrl and DashboardRecentActivityCtrl... unused now!
};
