(function () {

  'use strict';

  angular.module('drmc.services').service('ModalEditDcMetadataService', function ($modal, SETTINGS) {

    var configuration = {
      templateUrl: SETTINGS.viewsPath + '/modals/edit-dc-metadata.html',
      backdrop: true,
      controller: 'EditDcMetadataCtrl',
      windowClass: 'modal-large',
      resolve: {}
    };

    var open = function (options) {
      options = options || {};

      configuration.resolve.id = function () {
        return angular.isDefined(options.id) ? options.id : null;
      };

      configuration.resolve.parentId = function () {
        return angular.isDefined(options.parentId) ? options.parentId : null;
      };

      return $modal.open(configuration);
    };

    this.create = function (parentId) {
      return open({ parentId: parentId });
    };

    this.edit = function (id) {
      return open({ id: id });
    };

  });

})();
