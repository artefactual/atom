'use strict';

module.exports = function ($scope, $modalInstance, sources, target) {

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.modalContainer = {};

  // Associative relationships types
  $scope.types = [
    { id: 1, name: 'hasVersion' },
    { id: 2, name: 'hasPart' },
    { id: 3, name: 'hasFormat' },
    { id: 4, name: 'hasVersion' },
    { id: 5, name: 'isReferencedBy' },
    { id: 6, name: 'isReplacedBy' },
    { id: 7, name: 'isRequiredBy' },
    { id: 8, name: 'conformsTo' }
  ];

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
