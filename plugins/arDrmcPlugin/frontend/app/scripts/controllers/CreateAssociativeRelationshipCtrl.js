'use strict';

module.exports = function ($scope, $modalInstance, InformationObjectService, TaxonomyService, id, source, target) {

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.modalContainer = {};

  // New record?
  $scope.new = id === null;

  // Resource (TODO: this needs some rearrangements!)
  // $scope.source: { id: ..., label: ... }
  // $scope.target: { id: ..., label: ... }
  // id (if editing)
  // $scope.modalContainer.obj.type
  // $scope.modalContainer.obj.description

  // Associative relationships types
  TaxonomyService.getTerms('ASSOCIATIVE_RELATIONSHIP_TYPES').then(function (data) {
    $scope.modalContainer.types = data.terms;
  });

  if ($scope.new) {
    $scope.source = source;
    $scope.target = target;
  } else {
    InformationObjectService.getAssociation(id).then(function (response) {
      var data = response.data;
      $scope.source = {
        id: data.subject.id,
        label: data.subject.title
      };
      $scope.target = {
        id: data.object.id,
        label: data.object.title
      };
      $scope.modalContainer.obj = $scope.modalContainer.obj || {};
      if (data.hasOwnProperty('type') && data.type.hasOwnProperty('id')) {
        $scope.modalContainer.obj.type = data.type.id;
      }
      if (data.hasOwnProperty('description')) {
        $scope.modalContainer.obj.description = data.description;
      }
    }, function (response) {
      console.log(response.statusText);
      $modalInstance.dismiss('Object not found');
    });
  }

  $scope.submit = function () {
    if ($scope.modalContainer.form.$invalid) {
      return;
    }
    var options = {};
    if (angular.isDefined($scope.modalContainer.obj.description)) {
      options.description = $scope.modalContainer.obj.description;
    }
    if ($scope.new) {
      InformationObjectService.associate(source.id, target.id, $scope.modalContainer.obj.type, options).then(function (response) {
        $modalInstance.close(response.data.id, $scope.modalContainer.obj.type);
      }, function () {
        $modalInstance.dismiss('Error');
      });
    } else {
      options.type_id = $scope.modalContainer.obj.type;
      InformationObjectService.updateAssociation(id, options).then(function () {
        $modalInstance.close(id, $scope.modalContainer.obj.type);
      }, function () {
        $modalInstance.dismiss('Error');
      });
    }
  };

  $scope.delete = function ($event) {
    // I don't understand why the default is to submit?
    $event.preventDefault();
    InformationObjectService.deleteAssociation(id).then(function () {
      $modalInstance.close('deleted');
    }, function (response) {
      console.log('Delete failed:', response.statusText);
    });
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('Cancel');
  };

};
