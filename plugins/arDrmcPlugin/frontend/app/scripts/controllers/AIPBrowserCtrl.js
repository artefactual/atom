'use strict';

module.exports = function ($scope, $modal, $q, ATOM_CONFIG, AIPService) {

  AIPService.getAIPs()
    .success(function (data) {
      $scope.data = data;
    });

  $scope.classifications = [
    { id: 1, name: 'Artwork' },
    { id: 2, name: 'Option 2' },
    { id: 3, name: 'Option 3' }
  ];

  $scope.openReclassifyModal = function (aip) {
    // New scope just for the modal
    var modalScope = $scope.$new();
    modalScope.aip = aip;

    // Open modal and store a reference (a promise)
    $scope.reclassifyModal = $modal({
      template: ATOM_CONFIG.viewsPath + '/partials/reclassify-aips.html',
      persist: true,
      show: true,
      backdrop: 'static',
      scope: modalScope
    });
  };

  $scope.reclassifyAIP = function ($scope, aip, classification) {
    if (classification === undefined) {
      $scope.showOverview = true;
    }

    $q.when($scope.reclassifyModal).then(function (modalEl) {
      AIPService.reclassifyAIP(aip.id, classification)
        .success(function () {
          // Update AIP and close modal
          aip.class = classification;
          modalEl.modal('hide');
        }).error(function () {
          // Close modal
          modalEl.modal('hide');
        });
    });

  };

  // Support overview toggling
  $scope.showOverview = true;
  $scope.toggleOverview = function () {
    $scope.showOverview = !$scope.showOverview;
  };

  // Alerts
  $scope.alerts = [
    {
      'type': 'alert',
      'title': 'Uh-oh!',
      'content': 'Please enter a classification'
    }, {
      'type': 'info',
      'title': 'Heads up!',
      'content': 'More info needed, please.'
    }
  ];
};
