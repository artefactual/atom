'use strict';

module.exports = function ($scope, $stateParams) {

  /*ReportsService.reportsViewData().then(function (data) {
    $scope.viewData = data;
  });*/

  /*if (angular.isDefined(routeData)) {
    $scope.highLevelIngest = routeData.data;
    $scope.reportMeta = {
      title: 'High Level Ingest',
      category: 'Activity Report'
    };
  }*/

  if (angular.isDefined($stateParams.slug)) {
    //savedSearch();
    //isSavedSearchLoaded = true;
    console.log('stateparams is defined', $stateParams);
  } else {
    // search();
  }

  /* var search = function () {
    SearchService.search($stateParams.entity, $scope.criteria)
      .then(function (response) {
        $scope.data = response.data;
        $scope.$broadcast('pull.success', response.data.total);
      }, function (reason) {
        console.log('Failed', reason);
        delete $scope.data;
      });
  };*/

  /*var savedSearch = function () {
    SearchService.getSearchBySlug($stateParams.slug).then(function (response) {
      $scope.name = response.name;
      console.log($scope.name);
      $stateParams.entity = $scope.selectedEntity = response.type;
      $scope.criteria = response.criteria;
      $scope.include = SETTINGS.viewsPath + '/' + response.type + '.search.html';
      SearchService.setQuery(response.criteria.query);
      search();
    }, function (response) {
      throw response;
    });
  };*/

};
