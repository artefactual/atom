'use strict';

/**
 * SearchCtrl contains the business logic for all our share pages. It shares
 * state with other controllers via SearchService.
 */
module.exports = function ($scope, $stateParams, SearchService, $filter) {
  // Search state
  $scope.criteria = {};
  $scope.criteria.limit = 10;
  $scope.page = 1; // Don't delete this, it's an important default for the loop

  // Reference to types of searches
  $scope.tabs = SearchService.searches;

  // Changes in scope.page updates criteria.skip
  $scope.$watch('page', function (value) {
    $scope.criteria.skip = (value - 1) * $scope.criteria.limit;
  });

  // TODO: watch changes only in SearchService.query, not working for me!
  // Instead of a pull mechanism, I could push changes using $broadcast...
  $scope.$watch(function () {
    $scope.criteria.query = SearchService.query;
  });

  // Watch for criteria changes
  $scope.$watch('criteria', function () {
    search();
  }, true); // check properties when watching

  // Perfom query after entity changes
  $scope.$watch($stateParams.entity, function (newVal, oldVal) {
    if (newVal === oldVal) {
      return;
    }
    search();
  });

  function search () {
    SearchService.search($stateParams.entity, $scope.criteria)
      .then(function (response) {
        $scope.data = response.data;
        $scope.$broadcast('pull.success', response.data.total);
      }, function (reason) {
        console.log('Failed', reason);
        delete $scope.data;
      });
  }

  $scope.getTermLabel = function (facet, id) {
    if (undefined === $scope.data) {
      return id;
    }
    for (var term in $scope.data.facets[facet].terms) {
      if (parseInt($scope.data.facets[facet].terms[term].term) === parseInt(id)) {
        return $scope.data.facets[facet].terms[term].label;
      }
    }
  };

  $scope.getSizeRangeLabel = function (from, to) {
    if (typeof from !== 'undefined' && typeof to !== 'undefined') {
      return 'Between ' + $filter('UnitFilter')(from) + ' and ' + $filter('UnitFilter')(to);
    }
    if (typeof from !== 'undefined') {
      return 'Bigger than ' + $filter('UnitFilter')(from);
    }
    if (typeof to !== 'undefined') {
      return 'Smaller than ' + $filter('UnitFilter')(to);
    }
  };
};
