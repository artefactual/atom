'use strict';

module.exports = function ($scope, $state, ModalSaveSearchService, SearchService) {

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
      $scope.$parent.search();
      $scope.selectedSearches = [];
    });
  };

  var _delete = function (id) {
    SearchService.deleteSearch(id).then(function () {
      console.log('Deleted search ' + id);
    }, function () {
      throw 'Error deleting search ' + id;
    });
  };

  $scope.delete = function () {
    for (var key in $scope.selectedSearches) {
      _delete($scope.selectedSearches[key]);
    }
    $scope.$parent.search();
    $scope.selectedSearches = [];
  };

};
