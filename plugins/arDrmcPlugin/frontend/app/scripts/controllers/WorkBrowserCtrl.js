'use strict';

module.exports = function ($scope, InformationObjectService) {
  // criteria contain GET params used when calling getAIPs to refresh data
  $scope.criteria = {};
  $scope.criteria.limit = 10;
  $scope.criteria.sort = 'name';
  $scope.page = 1; // Don't delete this, it's an important default for the loop

  // Changes in scope.page updates criteria.skip
  $scope.$watch('page', function (value) {
    $scope.criteria.skip = (value - 1) * $scope.criteria.limit;
  });

  // Watch for criteria changes
  $scope.$watch('criteria', function () {
    $scope.pull();
  }, true); // check properties when watching

  $scope.pull = function () {
    InformationObjectService.getWorks($scope.criteria)
      .then(function (response) {
        $scope.data = response.data;
        $scope.$broadcast('pull.success', response.data.total);
      }, function (reason) {
        console.log('Failed', reason);
      });
  };

  $scope.search = function () {
    $scope.criteria.query = $scope.query;
  };

  $scope.clearSearch = function () {
    delete $scope.query;
    delete $scope.criteria.query;
  };
};
