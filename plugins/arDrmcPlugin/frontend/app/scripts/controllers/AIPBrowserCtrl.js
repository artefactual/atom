'use strict';

module.exports = function ($scope, $modal, $q, ATOM_CONFIG, AIPService) {

  // DEBUGGING: make it so we can examine scope from browser
  window.scope = $scope;

  // criteria contain GET params used when calling getAIPs to refresh data
  $scope.criteria = {};

  $scope.data = {};
  $scope.data.aips = {};
  $scope.data.aips.results = { foo: 'bar' };

  // watch for criteria changes
  $scope.$watch('criteria', function () {
    $scope.pull();
  }, true); // check properties when watching

  $scope.pull = function () {
    AIPService.getAIPs($scope.criteria)
      .success(function (data) {
        $scope.data = data;
      });
  };

  $scope.classifications = [
    { id: 10, name: 'Artwork Component' },
    { id: 20, name: 'Artwork Material' },
    { id: 30, name: 'Supporting Documentation' },
    { id: 40, name: 'Supporting Technology' }
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

  $scope.open = function (aip) {

    $scope.aip = aip;

    var modalInstance = $modal.open({
      templateUrl: ATOM_CONFIG.viewsPath + '/partials/reclassify-aips.html',
      backdrop: true,
      controller: function ($scope, $modalInstance) {

        $scope.reclassify = function () {
          AIPService.reclassifyAIP($scope.aip.id, $scope.aip.class)
            .success(function () {
              $scope.pull();
              $modalInstance.close($scope.aip.class);
            }).error(function () {
              $modalInstance.dismiss('Your new classification could not be assigned');
            });
        };

        $scope.cancel = function () {
          $modalInstance.dismiss('cancel');
        };

      },
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
};
