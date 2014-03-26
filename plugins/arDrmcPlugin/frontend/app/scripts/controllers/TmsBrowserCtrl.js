'use strict';

module.exports = function ($scope, $http) {

  $scope.search = function () {
    if (!$scope.query || $scope.query.length < 1) {
      delete $scope.results;
      delete $scope.thumbnail;
      return;
    }
    $http.get('http://vmdrmcatom02.museum.moma.org/tms/TMSAPI/TmsObjectSvc/TmsObjects.svc/GetTombstoneDataRest/ObjectID/' + $scope.query)
      .then(function (response) {
        $scope.results = response.data.GetTombstoneDataRestIdResult;
        $scope.thumbnail = response.data.GetTombstoneDataRestIdResult.Thumbnail;
        console.log($scope.thumbnail);
      });
  };

};
