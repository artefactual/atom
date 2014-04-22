'use strict';

module.exports = function ($scope, $q, StatisticsService) {

  /**
   * Run queries parallely
   */
  var pull = function () {
    var downloadActivity = StatisticsService.getDownloadActivity();
    var ingestionActivity = StatisticsService.getIngestionActivity();
    var ingestionSummary = StatisticsService.getIngestionSummary();
    var artworkByMonthSummary = StatisticsService.getArtworkByMonthSummary();

    $q.all([downloadActivity, ingestionActivity, ingestionSummary, artworkByMonthSummary]).then(function (responses) {
      $scope.downloadActivity = responses[0].data.results;
      $scope.ingestionActivity = responses[1].data.results;
      $scope.ingestionSummary = responses[2].data.results;
      $scope.artworkByMonthSummary = responses[3].data.results;

      var collection = [
        {
          'type': 'Artworks',
          'count': 935
        },
        {
          'type': 'Components',
          'count': 10346
        },
        {
          'type': 'AIPs',
          'count': 4789
        },
        {
          'type': 'Files',
          'count': 135526
        },
        {
          'type': 'Supporting technologies',
          'count': 150
        }
      ];
      $scope.storageCodecCollection = collection;
    });
  };

  pull();

  // TODO: DashboardIngestionCtrl and DashboardRecentActivityCtrl... unused now!

};
