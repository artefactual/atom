(function () {

  'use strict';

  angular.module('drmc.controllers').controller('SearchSearchCtrl', function ($scope, $state, $q, ModalSaveSearchService, SearchService) {

    // Default sorting options
    $scope.criteria.sort_direction = 'desc';
    $scope.criteria.sort = 'createdAt';

    // Store ids of searches with checkbox selected
    $scope.selectedSearches = [];

    // Toggle selected search
    $scope.toggleSelection = function (id) {
      var index = $scope.selectedSearches.indexOf(id);
      if (index > -1) {
        $scope.selectedSearches.splice(index, 1);
      } else {
        $scope.selectedSearches.push(id);
      }
    };

    $scope.edit = function () {
      ModalSaveSearchService.edit($scope.selectedSearches[0]).result.then(function () {
        $scope.$parent.updateResults();
        $scope.selectedSearches = [];
      });
    };

    $scope.delete = function () {
      var queries = [];
      for (var key in $scope.selectedSearches) {
        var id = $scope.selectedSearches[key];
        queries.push(SearchService.deleteSearch(id));
      }
      $q.all(queries).then(function () {
        $scope.$parent.updateResults();
        $scope.selectedSearches = [];
      });
    };

  });

})();
