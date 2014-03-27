'use strict';

/**
 * SearchCtrl contains the business logic for all our share pages. It shares
 * state with other controllers via SearchService.
 */
module.exports = function ($scope, $stateParams, SearchService) {
  // TODO: watch changes only in SearchService.query, not working for me!
  // Instead of a pull mechanism, I could push changes using $broadcast...
  $scope.$watch(function () {
    $scope.query = SearchService.query;
  });

  $scope.$watch('query', function (newValue, oldValue) {
    if (newValue && newValue !== oldValue) {
      search();
    }
  });

  function search () {
    SearchService.search($scope.query, $stateParams.entity);
  }
};
