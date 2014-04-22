'use strict';

module.exports = function ($scope, ModalReclassifyAipService) {

  $scope.openReclassifyModal = function (aip) {
    ModalReclassifyAipService.open(aip.uuid, aip.part_of.title).result.then(function (data) {
      aip.type.id = data.type_id;
      aip.type.name = data.type;
    });
  };

};
