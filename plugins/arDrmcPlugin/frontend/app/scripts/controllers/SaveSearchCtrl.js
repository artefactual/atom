'use strict';

module.exports = function ($scope, $rootScope, $state, $stateParams, $modalInstance, SETTINGS, id, criteria, entity, SearchService) {

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.modalContainer = {};

  // New record?
  $scope.new = id === null;

  // Initialize
  if ($scope.new) {
    $scope.resource = {};
    $scope.resource.criteria = criteria;
    $scope.resource.type = entity;
  } else {
    SearchService.getSearchById(id).then(function (response) {
      $scope.resource = response;
    });
  }

  // Title, based in new
  if ($scope.new) {
    $scope.title = 'Save search';
  } else {
    $scope.title = 'Edit search';
  }

  // Update existing record
  var update = function () {
    SearchService.updateSearch($scope.resource.id, $scope.resource).then(function () {
      $modalInstance.close();
    }, function () {
      $modalInstance.dismiss('Search could not be saved');
    });
    $rootScope.$broadcast('searchesChanged');
  };

  // Create new record
  var create = function () {
    SearchService.createSearch($scope.resource).then(function () {
      $modalInstance.close();
    }, function () {
      $modalInstance.dismiss('Search could not be created');
    });
  };

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  // Form submission callback
  $scope.submit = function () {
    if ($scope.modalContainer.form.$invalid) {
      return;
    }
    if ($scope.new) {
      create();
    } else {
      update();
    }
  };
};
