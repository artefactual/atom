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

    configuration.resolve.entity = function () {
      return angular.isDefined(options.entity) ? options.entity : null;
    };

    return $modal.open(configuration);
  };

  this.create = function (criteria, entity) {
    return open({ criteria: criteria, entity: entity });
  };

  this.edit = function (id) {
    return open({ id: id });
  };
};
