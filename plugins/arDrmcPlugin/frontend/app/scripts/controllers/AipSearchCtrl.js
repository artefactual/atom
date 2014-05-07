'use strict';

module.exports = function ($scope, ModalReclassifyAipService, SETTINGS) {

  // Default sorting options
  $scope.criteria.sort_direction = 'desc';
  $scope.criteria.sort = 'createdAt';

  // Levels of description to determine part_of link
  $scope.artworkId = parseInt(SETTINGS.drmc.lod_artwork_record_id);
  $scope.techId = parseInt(SETTINGS.drmc.lod_supporting_technology_record_id);

  $scope.openReclassifyModal = function (aip) {
    ModalReclassifyAipService.open(aip.uuid, aip.part_of.title).result.then(function (data) {
      aip.type = aip.type || {};
      aip.type.id = data.type_id;
      aip.type.name = data.type;
    });
  };

};
