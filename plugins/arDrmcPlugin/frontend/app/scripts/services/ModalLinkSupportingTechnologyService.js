'use strict';

module.exports = function ($modal, SETTINGS) {
  this.open = function (id) {
    var configuration = {
      templateUrl: SETTINGS.viewsPath + '/modals/link-supporting-technology.html',
      backdrop: 'static',
      controller: 'LinkSupportingTechnologyCtrl',
      resolve: {
        id: function () {
          return id;
        }
      }
    };
    return $modal.open(configuration);
  };

  // Close the dialog
  this.cancel = function () {
    $modal.dismiss('cancel');
  };
};
