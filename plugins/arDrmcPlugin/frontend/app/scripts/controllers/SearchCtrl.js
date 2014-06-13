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

  // Reference to types of searches
  $scope.tabs = SearchService.searches;

  // Changes in scope.page updates criteria.skip
  $scope.$watch('page', function (value) {
    $scope.criteria.skip = (value - 1) * $scope.criteria.limit;
  });

  // Put a watcher on SearchService.query and update the criteria object.
  $scope.$watch(function () {
    return SearchService.query;
  }, function () {
    $scope.criteria.query = SearchService.query;
  });

  // We want to load the saved search only once. Next loop may be only an update
  // of the criteria. TODO: Actually, once the user updates the query we should
  // probably close the saved search and stop showing to the user the saved
  // search name.
  var isSavedSearchLoaded = false;

  // Watch for criteria changes
  $scope.$watch('criteria', function () {
    if (angular.isDefined($stateParams.slug) && !isSavedSearchLoaded) {
      savedSearch();
      isSavedSearchLoaded = true;
    } else {
      search();
    }
  }, true);

  var search = function () {
    SearchService.search($stateParams.entity, $scope.criteria)
      .then(function (response) {
        $scope.data = response.data;
        $scope.$broadcast('pull.success', response.data.total);
      }, function (reason) {
        console.log('Failed', reason);
        delete $scope.data;
      });
  };

  var savedSearch = function () {
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
  };

  $scope.unsetQuery = function () {
    SearchService.setQuery(null, $stateParams.entity);
  };

  // Support overview toggling (AIPs and searches)
  $scope.showOverview = true;
  $scope.toggleOverview = function () {
    $scope.showOverview = !$scope.showOverview;
  };

  // Saved search modal
  $scope.openSaveSearchModal = function (criteria) {
    ModalSaveSearchService.create(criteria, $scope.selectedEntity);
  };

  // Remove query when leaving loaded search
  $scope.$on('$locationChangeSuccess', function (event, next, current) {
    if (current.indexOf('/search/saved/') !== -1) {
      SearchService.setQuery(null, $stateParams.entity);
    }
  });


  /**
   * Facets
   */

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

  $scope.updateResults = function () {
    search();
  };

};
