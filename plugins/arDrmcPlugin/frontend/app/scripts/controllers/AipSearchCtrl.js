'use strict';

module.exports = function ($scope, ModalReclassifyAipService) {

  // Default sorting options
  $scope.criteria.sort_direction = 'asc';
  $scope.criteria.sort = 'createdAt';

  $scope.openReclassifyModal = function (aip) {
    ModalReclassifyAipService.open(aip.uuid, aip.part_of.title).result.then(function (data) {
      aip.type = aip.type || {};
      aip.type.id = data.type_id;
      aip.type.name = data.type;
    });
  };

  // Support AIP overview toggling
  $scope.showOverview = true;
  $scope.toggleOverview = function () {
    $scope.showOverview = !$scope.showOverview;
  };

};
