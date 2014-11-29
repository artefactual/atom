(function () {

  'use strict';

  angular.module('drmc.controllers').controller('AIPReclassifyCtrl', function ($scope, $modalInstance, AIPService, TaxonomyService, uuid) {

    $scope.modalContainer = {};

    AIPService.getAIP(uuid).then(function (response) {
      $scope.modalContainer.type_id = response.data.type.id;
      $scope.modalContainer.part_of_id = response.data.part_of.id;
      $scope.modalContainer.part_of_title = response.data.part_of.title;
      $scope.modalContainer.name = response.data.name;
    });

    TaxonomyService.getTerms('AIP_TYPES')
      .then(function (types) {
        $scope.classifications = types.terms;
      });

    $scope.reclassify = function () {
      AIPService.reclassifyAIP(uuid, $scope.modalContainer.type_id)
        .success(function () {
          var r = {
            type_id: $scope.modalContainer.type_id
          };
          for (var i = 0; i < $scope.classifications.length; i++) {
            var current = $scope.classifications[i];
            if (current.id === $scope.modalContainer.type_id) {
              r.type = current.name;
              break;
            }
          }
          $modalInstance.close(r);
        }).error(function () {
          $modalInstance.dismiss('failed');
        });
    };

    $scope.cancel = function () {
      $modalInstance.dismiss('cancel');
    };

  });

})();
