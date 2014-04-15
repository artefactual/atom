'use strict';

module.exports = function ($modal, SETTINGS) {
  var configuration = {
    templateUrl: SETTINGS.viewsPath + '/modals/digital-object-viewer.html',
    backdrop: true,
    resolve: {},
    controller: 'DigitalObjectViewerCtrl'
  };

  this.open = function (files) {
    configuration.resolve.files = function () {
      return files;
    };
    return $modal.open(configuration);
  };
};
