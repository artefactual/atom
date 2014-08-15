'use strict';

module.exports = function ($modal, SETTINGS, InformationObjectService, ModalLinkSupportingTechnologyService) {
  return {
    restrict: 'E',
    templateUrl: SETTINGS.viewsPath + '/partials/context-browser.artwork.html',
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
       * cbd events (specific to artworks)
       */

      scope.cb.events.on('click-supporting-technology-icon', function (attrs) {
        scope.$apply(function (scope) {
          scope.crudRelatedTechnologies(attrs.id);
        });
      });


      /**
       * TMS metadata
       */

      scope.tmsCollapsed = false;

      scope.tmsFieldNameMap = {
        componentName: 'Name',
        componentType: 'Component type',
        componentNumber: 'Number',
        type: 'Type',
        status: 'Status',
        mediaFormat: 'Media format'
      };


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
        InformationObjectService.create(data).then(function (response) {
          scope.cb.addNode(response.id, label, 'description', response.parent_id);
        });
      };

      scope.isDeletable = function (node) {
        if (typeof node === 'undefined' || typeof node.data === 'undefined') {
          return false;
        }
        return scope.cb.graph.predecessors(node.id).length === 0 && !InformationObjectService.hasTmsOrigin(node.data.level_of_description_id);
      };

      scope.crudRelatedTechnologies = function (id) {
        ModalLinkSupportingTechnologyService.open(id).result.then(function () {
          scope.pull();
        });
      };

    }
  };
};
