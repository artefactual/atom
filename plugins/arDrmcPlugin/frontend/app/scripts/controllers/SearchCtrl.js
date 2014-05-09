'use strict';

/**
 * SearchCtrl contains the business logic for all our share pages. It shares
 * state with other controllers via SearchService.
 */
module.exports = function ($scope, $stateParams, SearchService, $filter, ModalSaveSearchService, SETTINGS) {
  $scope.criteria = {};
  $scope.criteria.limit = 10;
  $scope.page = 1; // Don't delete this, it's an important default for the loop
  $scope.selectedEntity = $stateParams.entity;

  // If coming with slug: load template, obtain search, load values and make search
  if ($stateParams.slug !== undefined) {
    SearchService.getSearchBySlug($stateParams.slug).then(function (response) {
      $scope.name = response.name;
      $stateParams.entity = $scope.selectedEntity = response.type;
      $scope.criteria = response.criteria;
      $scope.include = SETTINGS.viewsPath + '/' + response.type + '.search.html';
      SearchService.setQuery(response.criteria.query);
      $scope.search();
    }, function (response) {
      throw response;
    });
  }

  // Remove query when leaving loaded search
  $scope.$on('$locationChangeSuccess', function (event, next, current) {
    if (current.indexOf('/search/saved/') !== -1) {
      $scope.criteria.query = undefined;
      SearchService.setQuery($scope.criteria.query);
    }
  });

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
    $scope.search();
  }, true); // check properties when watching

  $scope.search = function () {
    SearchService.search($stateParams.entity, $scope.criteria)
      .then(function (response) {
        $scope.data = response.data;
        $scope.$broadcast('pull.success', response.data.total);
      }, function (reason) {
        console.log('Failed', reason);
        delete $scope.data;
      });
  };

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

  $scope.getDateRangeLabel = function (facet, from, to) {
    for (var range in $scope.data.facets[facet].ranges) {
      if ($scope.data.facets[facet].ranges[range].from === from && $scope.data.facets[facet].ranges[range].to === to) {
        return $scope.data.facets[facet].ranges[range].label;
      }
    }
  };

  $scope.unsetQuery = function () {
    $scope.criteria.query = undefined;
    SearchService.setQuery($scope.criteria.query, $stateParams.entity);
    $scope.search();
  };

  $scope.openSaveSearchModal = function (criteria) {
    ModalSaveSearchService.create(criteria, $scope.selectedEntity);
  };

  // Support overview toggling (AIPs and searches)
  $scope.showOverview = true;
  $scope.toggleOverview = function () {
    $scope.showOverview = !$scope.showOverview;
  };

  $scope.$on('searchesChanged', function () {
    $scope.search();
  });

};
