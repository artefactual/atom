'use strict';

module.exports = function ($scope, $modal, $q, ATOM_CONFIG, AIPService) {

  // DEBUGGING: make it so we can examine scope from browser
  window.scope = $scope;

  // criteria contain GET params used when calling getAIPs to refresh data
  $scope.criteria = {};

  // watch for criteria changes
  $scope.$watch('criteria', function () {
    $scope.pull();
  }, true); // check properties when watching

  $scope.pull = function () {
    AIPService.getAIPs($scope.criteria)
      .success(function (data) {
        $scope.data = data;
        console.log($scope.data);
      });
  };

  // TODO: Load from server (/taxonomy endpoint?)
  $scope.classifications = {
    1: 'Artwork component',
    2: 'Artwork material',
    3: 'Supporting documentation',
    4: 'Supporting technology',
    5: 'Unclassified'
  };

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

  $scope.openReclassifyModal = function (aip) {
    // Current AIP selected equals to AIP in the modal
    $scope.aip = aip;
    // It happens that $modal.open returns a promise :)
    var modalInstance = $modal.open({
      templateUrl: ATOM_CONFIG.viewsPath + '/partials/reclassify-aips.html',
      backdrop: true,
      controller: 'AIPReclassifyCtrl',
      scope: $scope, // TODO: isolate with .new()?
      resolve: {
        classifications: function () {
          return $scope.classifications;
        }
      }
    });
    // This is going to happen only if the $modal succeeded
    modalInstance.result.then(function (result) {
      aip.class = result;
    });
  };
};
