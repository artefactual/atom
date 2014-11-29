(function () {

  'use strict';

  angular.module('drmc.services').service('ModalReclassifyAipService', function ($modal, SETTINGS) {

    var configuration = {
        templateUrl: SETTINGS.viewsPath + '/modals/reclassify-aips.html',
        backdrop: true,
        controller: 'AIPReclassifyCtrl',
        resolve: {}
      };

    this.open = function (uuid, part_of) {
      configuration.resolve.uuid = function () {
        return uuid;
      };
      configuration.resolve.part_of = function () {
        return part_of;
      };
      return $modal.open(configuration);
    };

  });

})();
