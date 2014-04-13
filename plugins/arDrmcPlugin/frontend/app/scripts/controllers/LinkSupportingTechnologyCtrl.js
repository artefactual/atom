'use strict';

module.exports = function ($scope, $modalInstance, InformationObjectService, id) {

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.modalContainer = {};

  var pull = function () {
    InformationObjectService.getById(id).then(function (response) {
      $scope.title = response.data.title;
    });
    InformationObjectService.getSupportingTechnologyRecordsOf(id).then(function (data) {
      $scope.relationships = data;
    });
    $scope.dcRelationTypes = [
      { id: 1, name: 'isPartOf' },
      { id: 2, name: 'isFormatOf' },
      { id: 3, name: 'isVersionOf' },
      { id: 4, name: 'references' },
      { id: 5, name: 'requires' }
    ];
  };

  pull();

  // Form submission callback
  $scope.submit = function () {
    if ($scope.modalContainer.form.$invalid) {
      return;
    }
  };

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  $scope.searchSupportingTechnology = function (viewValue) {
    return InformationObjectService.getSupportingTechnologyRecords({ query: viewValue }).then(function (response) {
      var matches = [];
      angular.forEach(response.data.results, function (item) {
        matches.push(item);
      });
      return matches;
    });
  };

  $scope.onSelectSupportingTechnology = function (item) {
    var found = $scope.relationships.some(function (i) {
      return item.id === i.id;
    });
    if (found) {
      return;
    }
    $scope.relationships.push({
      object_id: id,
      name: item.title
    });
  };

  $scope.delete = function (index) {
    $scope.relationships.splice(index, 1);
  };

  $scope.deleteAll = function () {
    $scope.relationships = [];
  };

  $scope.getRelationName = function (typeId) {
    if (angular.isUndefined(typeId)) {
      return '';
    }
    for (var i = 0; i < $scope.dcRelationTypes.length; i++) {
      var type = $scope.dcRelationTypes[i];
      if (type.id === typeId) {
        return type.name;
      }
    }
    return '?';
  };

};
