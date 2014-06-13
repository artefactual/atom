'use strict';

module.exports = function (SETTINGS, InformationObjectService, ModalEditDcMetadataService) {
  return {
    restrict: 'E',
    templateUrl: SETTINGS.viewsPath + '/partials/context-browser.technology.html',
    scope: {
      id: '@',
      user: '='
    },
    controller: 'ContextBrowserCtrl',
    replace: true,
    transclude: true,
    link: function (scope) {

      /**
       * Dublin Core metadata
       */

      scope.dcCollapsed = false;


      /**
       * Node actions
       */

      scope.selectNode = function (id) {
        scope.currentNode = scope.activeNodes[id] = { id: id };
        // Fetch information from the server
        InformationObjectService.getById(id).then(function (response) {
          scope.currentNode.data = response.data;
          // Check if there are DC fields
          scope.currentNode.hasDc = Object.keys(scope.currentNode.data).some(function (element) {
            if (element === 'title') {
              return false;
            }
            return -1 < scope.dcFields.indexOf(element);
          });
        });
      };

      scope.addChildNode = function (parentId) {
        // TODO: Use a modal
        var label = prompt('Insert label');
        if (!label) {
          return;
        }
        var data = {
          title: label,
          parent_id: parentId,
          level_of_description: 'Description'
        };
        // TODO: Should we use ModalEditDcMetadataService.create()?
        InformationObjectService.createSupportingTechnologyRecord(data).then(function (response) {
          scope.cb.addNode(response.id, label, 'supporting-technology-record', response.parent_id);
        });
      };

      scope.editNode = function (id) {
        ModalEditDcMetadataService.edit(id).result.then(function () {
          scope.pull();
        });
      };

      scope.isDeletable = function (node) {
        if (typeof node === 'undefined' || typeof node.data === 'undefined') {
          return false;
        }
        return scope.cb.graph.predecessors(node.id).length === 0;
      };

    }
  };
};
