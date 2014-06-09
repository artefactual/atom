'use strict';

module.exports = function ($scope, $modalInstance, TaxonomyService, sources, target) {

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.modalContainer = {};

  // Associative relationships types
  TaxonomyService.getTerms('ASSOCIATIVE_RELATIONSHIP_TYPES').then(function (data) {
    $scope.modalContainer.types = data.terms;
  });

  // Make modal resolves accessible from the model
  $scope.sources = sources;
  $scope.target = target;

  $scope.submit = function () {
    if ($scope.modalContainer.form.$invalid) {
      return;
    }
    // modalContainer.type
    // modalContainer.note
    $modalInstance.close();
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('Cancel');
  };

};
