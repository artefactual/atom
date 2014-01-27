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

  $scope.reclassifyAIP = function (aip, classification) {
    if (classification === undefined) {
      throw 'hey buddy what are you doing?';
    }

    $q.when($scope.reclassifyModal).then(function (modalEl) {
      AIPService.reclassifyAIP(aip.id, classification)
        .success(function (data, status) {
          console.log('OK', data, status);

          // Update object
          aip.class = classification;

          // Close the popup
          modalEl.modal('hide');


        }).error(function (data, status) {
          console.log('ERROR', data, status);
          modalEl.modal('hide');
        });
    });

  };

  // Support overview toggling
  $scope.showOverview = true;
  $scope.toggleOverview = function () {
    $scope.showOverview = !$scope.showOverview;
  };

};
