'use strict';

module.exports = function ($modal, SETTINGS) {
  var configuration = {
    templateUrl: SETTINGS.viewsPath + '/modals/save-search.html',
    backdrop: true,
    controller: 'SaveSearchCtrl',
    windowClass: 'modal-large',
    resolve: {}
  };

  var open = function (options) {
    options = options || {};

    configuration.resolve.id = function () {
      return angular.isDefined(options.id) ? options.id : null;
    };

    configuration.resolve.criteria = function () {
      return angular.isDefined(options.criteria) ? options.criteria : null;
    };

    return $modal.open(configuration);
  };

  this.create = function (criteria) {
    return open({ criteria: criteria });
  };

  this.edit = function (id) {
    return open({ id: id });
  };
};
