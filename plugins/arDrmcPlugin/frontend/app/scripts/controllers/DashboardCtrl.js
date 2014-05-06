'use strict';

module.exports = function ($scope, $q, StatisticsService) {

  $scope.dashboardGraphs = {};
  $scope.dashboardGraphs.radioModel = 'number';
  /**
   * Run queries parallely
   */

  $scope.pull = function () {
    //var downloadActivity = StatisticsService.getDownloadActivity();

    $scope.storageCodec = StatisticsService.getRunningTotalByFormats().then(function (response) {
      $scope.storageCodec = response.data.results;
    });

    $scope.$watch('storageCodec', function (oldVal, newVal) {
      if (newVal) {
      } else {
        return;
      }
    });


    /*$q.all([downloadActivity, storageCodec]).then(function (responses) {
      $scope.responses.downloadActivity = responses[0].data.results;
      $scope.responses.storageCodec = responses[1].data.results;
    });*/

  };
  $scope.pull();
  // TODO: DashboardIngestionCtrl and DashboardRecentActivityCtrl... unused now!

};
