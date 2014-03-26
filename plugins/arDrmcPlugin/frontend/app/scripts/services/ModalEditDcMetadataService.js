'use strict';

module.exports = function ($modal, SETTINGS) {
  var configuration = {
    templateUrl: SETTINGS.viewsPath + '/modals/edit-dc-metadata.html',
    backdrop: true,
    controller: 'EditDCMetadataCtrl',
    resolve: {}
  };

  var open = function () {
    return $modal.open(configuration);
  };

  this.create = function () {
    configuration.resolve.resource = function () {
      return false;
    };
    return open();
  };

  this.edit = function (resource) {
    configuration.resolve.resource = function () {
      return resource;
    };
    return open();
  };
};
