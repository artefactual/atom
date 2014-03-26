'use strict';

module.exports = function ($modal, SETTINGS) {
  var configuration = {
    templateUrl: SETTINGS.viewsPath + '/modals/digital-object-viewer.html',
    backdrop: true,
    controller: 'DigitalObjectViewerCtrl'
  };

  this.open = function () {
    return $modal.open(configuration);
  };
};
