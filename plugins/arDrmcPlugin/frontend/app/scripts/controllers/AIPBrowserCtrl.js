'use strict';

module.exports = function ($scope, $modal, $q, ATOM_CONFIG, AIPService) {

  // DEBUGGING: make it so we can examine scope from browser
  window.scope = $scope;

  // criteria contain GET params used when calling getAIPs to refresh data
  $scope.criteria = {};

  // watch for criteria changes
  $scope.$watch ('criteria', function () {
    console.log('Updating AIP data...');
    AIPService.getAIPs($scope.criteria)
      .success(function (data) {
        $scope.data = data;
      });
  }, true); // check properties when watching

  $scope.classifications = [
    { id: 1, name: 'Artwork Component' },
    { id: 2, name: 'Artwork Material' },
    { id: 3, name: 'Supporting Documentation' },
    { id: 4, name: 'Supporting Technology' }
  ];

  // Support overview toggling

  $scope.showOverview = true;
  $scope.toggleOverview = function () {
    $scope.showOverview = !$scope.showOverview;
  };

  // Ng-include logic

  $scope.templates = [
    { name: 'List View', url: ATOM_CONFIG.viewsPath + '/partials/aips.views.list.html' },
    { name: 'Browse View', url: ATOM_CONFIG.viewsPath + '/partials/aips.views.browse.html' }
  ];
  $scope.template = $scope.templates[0];

  // Sorting

  $scope.namePredicate = 'name';

  // Rotate icon

  $scope.isRotated = false;
  $scope.iconRotate = function () {
    $scope.isRotated = !$scope.isRotated;
  };

  $scope.open = function (aip) {

    $scope.aip = aip;

    var modalInstance = $modal.open({
      templateUrl: ATOM_CONFIG.viewsPath + '/partials/reclassify-aips.html',
      backdrop: true,
      controller: modalInstanceCtrl,
      scope: $scope,
      resolve: {
        classifications: function () {
          return $scope.classifications;
        }
      }
    });

    modalInstance.result.then(function (result) {
      aip.class = result;
    });
  };


  var modalInstanceCtrl = function ($scope, $q, $modalInstance) {

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
};
