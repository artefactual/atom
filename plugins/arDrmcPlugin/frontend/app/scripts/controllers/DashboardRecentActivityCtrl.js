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

    $q.all([downloadActivity, ingestionActivity]).then(function (results) {
     // console.log(results);
      $scope.downloadActivity = results[0].data;
      $scope.ingestionActivity = results[0].data;
      var summary = {};
      summary = [
          {
            artwork: '40 Part Motet',
            aip_title: 'disk image',
            size: '1 TB',
            ingested: '2014-03-17',
            user: 'poleksik'
          },
          {
            artwork: '10,000 Waves',
            aip_title: 'exhibition walkthrough',
            size: '40 GB',
            ingested: '2014-03-17',
            user: 'poleksik'
          },
          {
            artwork: 'Miniature in Black and White',
            aip_title: 'Slide scans',
            size: '363 MB',
            ingested: '2014-03-17',
            user: 'klewis'
          }
        ];
      console.log(summary);
      $scope.ingestionSummary = summary;
      $scope.artworkByMonthSummary = 'artwork';
    });

  };

  pull();
};
