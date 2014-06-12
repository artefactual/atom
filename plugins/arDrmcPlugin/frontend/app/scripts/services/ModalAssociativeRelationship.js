'use strict';

module.exports = function ($modal, SETTINGS) {
  var configuration = {
    templateUrl: SETTINGS.viewsPath + '/modals/create-associative-relationship.html',
    backdrop: true,
    controller: 'CreateAssociativeRelationshipCtrl',
    resolve: {}
  };

  // Parameters injected in the controller
  var params = ['id', 'sources', 'target'];

  var open = function (options) {
    options = options || {};
    // Construct resolve object
    params.forEach(function (param) {
      // Set to null if undefined to be consistent with the injection
      // requirements defined in the controller.
      configuration.resolve[param] = function () {
        return angular.isDefined(options[param]) ? options[param] : null;
      };
    });
    return $modal.open(configuration);
  };

  this.create = function (sources, target) {
    return open({
      sources: sources,
      target: target
    });
  };

  this.edit = function (id) {
    return open({
      id: id
    });
  };
};
