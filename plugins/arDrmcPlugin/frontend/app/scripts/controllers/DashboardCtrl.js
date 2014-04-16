'use strict';

module.exports = function ($scope, $q, StatisticsService) {

  /**
   * Run queries parallely
   */
  var pull = function () {
    var downloadActivity = StatisticsService.getDownloadActivity();
    var ingestionActivity = StatisticsService.getIngestionActivity();
    //var ingestionSummary = StatisticsService.getIngestionSummary();
    //var artworkByMonthSummary = StatisticsService.getArtworkByMonthSummary();

    $q.all([downloadActivity, ingestionActivity]).then(function (responses) {
      $scope.downloadActivity = responses[0].data.results;
      $scope.ingestionActivity = responses[1].data.results;
      //$scope.ingestionSummary = responses[2].data.results;
      //$scope.artworkByMonthSummary = responses[4].data.results;
      var download = [
        {
          file: 'Play Dead; Real time',
          reason: 'disk image',
          date: '2014-03-27',
          username: 'poleksik'
        },
        {
          file: 'Eve Online',
          reason: 'Slide scans',
          date: '2014-03-17',
          username: 'bfino'
        },
        {
          file: 'Portal',
          reason: 'exhibition walkthrough',
          date: '2014-03-04',
          username: 'bfino'
        }
      ];
      var activity = [
        {
          artwork_title: '40 Part Motet',
          aip_title: 'disk image',
          size_on_disk: '1 TB',
          created_at: '2014-03-17',
          user: 'bfino'
        },
        {
          artwork_title: '10,000 Waves',
          aip_title: 'exhibition walkthrough',
          size_on_disk: '40 GB',
          created_at: '2014-03-17',
          user: 'poleksik'
        },
        {
          artwork_title: 'Miniature in Black and White',
          aip_title: 'Slide scans',
          size_on_disk: '363 MB',
          created_at: '2014-03-17',
          user: 'klewis'
        }
      ];
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
      $scope.downloadActivity = download;
      $scope.ingestionActivity = activity;
      $scope.storageCodecCollection = collection;
    });
  };

  pull();

  // TODO: DashboardIngestionCtrl and DashboardRecentActivityCtrl... unused now!

};
