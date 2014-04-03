'use strict';

/**
 * SearchCtrl contains the business logic for all our share pages. It shares
 * state with other controllers via SearchService.
 */
module.exports = function ($scope, $stateParams, SearchService) {
  $scope.criteria = {};
  $scope.criteria.limit = 10;
  $scope.page = 1; // Don't delete this, it's an important default for the loop

  $scope.tabs = [
    { name: 'AIPs', entity: 'aips', active: $stateParams.entity === 'aips' },
    { name: 'Artwork records', entity: 'works', active: $stateParams.entity === 'works' },
    { name: 'Components', entity: 'components', active: $stateParams.entity === 'components' }
  ];

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
  $scope.$watch($stateParams.entity, function () {
    search();
  });

  function search () {
    if (null === $scope.criteria.query || $scope.criteria.query.length < 3) {
      delete $scope.data;
      return;
    }
    SearchService.search($stateParams.entity, $scope.criteria)
      .then(function (response) {
        $scope.data = response.data;
        $scope.$broadcast('pull.success', response.data.total);
      }, function (reason) {
        console.log('Failed', reason);
        delete $scope.data;
      });
  }
};
