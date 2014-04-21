'use strict';

module.exports = function ($scope, $modalInstance, InformationObjectService, TaxonomyService, id) {

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.modalContainer = {};

  var pull = function () {
    InformationObjectService.getById(id).then(function (response) {
      $scope.title = response.data.title;
    });
    InformationObjectService.getSupportingTechnologyRecordsOf(id).then(function (data) {
      $scope.relationships = data.results;
    });
    TaxonomyService.getTerms('SUPORTING_TECHNOLOGY_RELATION_TYPES').then(function (data) {
      $scope.dcRelationTypes = data.terms;
    });
  };

  pull();

  // Save changes
  $scope.save = function () {
    return InformationObjectService.setSupportingTechnologyRecords(id, $scope.relationships).then(function () {
      $modalInstance.close();
    });
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
      technology_record_id: item.id,
      name: item.title,
      type_id: $scope.dcRelationTypes[0].id
    });
    delete $scope.search;
  };

  $scope.delete = function (index) {
    $scope.relationships.splice(index, 1);
  };

  $scope.deleteAll = function () {
    $scope.relationships = [];
  };

};
