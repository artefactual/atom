(function () {

  'use strict';

  angular.module('drmc.directives').directive('arContextBrowserTechnology', function (SETTINGS, InformationObjectService, ModalEditDcMetadataService) {

    return {
      restrict: 'E',
      templateUrl: SETTINGS.viewsPath + '/partials/context-browser.technology.html',
      scope: {
        id: '@',
        user: '=',
        files: '=',
        _selectNode: '&onSelectNode'
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

  });

})();
