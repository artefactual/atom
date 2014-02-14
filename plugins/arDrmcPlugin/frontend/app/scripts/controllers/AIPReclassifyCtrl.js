'use strict';

module.exports = function ($scope, $modal,  $modalInstance, $q, AIPService) {

  $scope.reclassifyAIP = function (modalScope, classificationData) {

    $q.when(modalScope.modalInstance).then(function () {

      AIPService.reclassifyAIP ($scope.aip.id, classificationData.name)
        .success(function  (data, status, aip) {
          AIPService.getAIPs($scope.criteria);
          aip.class = classificationData.name;
          $modalInstance.close(classificationData.name);

        }).error(function (data, status) {
            console.error(data, status);
            $modalInstance.dismiss('Your new classification could not be assigned');
          });
    });

    $scope.cancel = function () {
      $modalInstance.dismiss('cancel');
    };
  };

};
