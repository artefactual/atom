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
      // Use only the first three items in the array
      if (files.length > 3) {
        files = files.slice(0, 3);
      }
      return files;
    };
    return $modal.open(configuration);
  };
};
