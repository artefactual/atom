'use strict';

module.exports = function ($modal, SETTINGS, InformationObjectService, ModalDigitalObjectViewerService, ModalLinkSupportingTechnologyService) {
  return {
    restrict: 'E',
    templateUrl: SETTINGS.viewsPath + '/partials/context-browser.artwork.html',
    scope: {
      id: '@',
      files: '=files',
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
       * File browser
       * TODO: make a directive for this
       */

      scope.fileListViewMode = 'list';
      scope.filesCollapsed = false;

      scope.hasFiles = function () {
        return typeof scope.files !== 'undefined' && scope.files.length > 0;
      };

      scope.hasSelectedFiles = function () {
        return scope.files.some(function (element) {
          return typeof element.selected !== 'undefined' && element.selected === true;
        });
      };

      scope.cancelFileSelection = function () {
        scope.files.forEach(function (element) {
          element.selected = false;
        });
      };

      scope.selectFile = function (file, $event, $index) {
        if ($event.shiftKey) {
          file.selected = !file.selected;
        } else {
          ModalDigitalObjectViewerService.open(scope.files, $index);
        }
      };

      scope.openAndCompareFiles = function () {
        // TODO: pass selected files
        ModalDigitalObjectViewerService.open(scope.files);
      };


      /**
       * TMS metadata
       */

      scope.tmsCollapsed = false;

      scope.tmsFieldNameMap = {
        componentName: 'Name',
        componentType: 'Component type',
        componentNumber: 'Number',
        type: 'Type'
      };


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
          // Invoke corresponding function injected in the scope
          scope._selectNode();
          // Retrieve a list of files or digital objects
          InformationObjectService.getDigitalObjects(id).then(function (response) {
            if (response.data.results.length > 0) {
              scope.files = response.data.results;
            } else {
              scope.files = [];
            }
          }, function () {
            scope.files = [];
          });
          // Retrieve TMS metadata for the component
          if (InformationObjectService.isComponent(scope.currentNode.data.level_of_description_id)) {
            InformationObjectService.getTms(id).then(function (response) {
              scope.currentNode.data.tms = response;
              // We don't need this
              delete scope.currentNode.data.tms.compCount;
              delete scope.currentNode.data.tms.componentID;
            });
          }
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
        InformationObjectService.create(data).then(function (response) {
          scope.cb.addNode(response.id, label, 'description', response.parent_id);
        });
      };

      scope.isDeletable = function (node) {
        if (typeof node.data === 'undefined') {
          return false;
        }
        return scope.cb.graph.predecessors(node.id).length === 0 && !InformationObjectService.hasTmsOrigin(node.data.level_of_description_id);
      };

      scope.linkNodes = function (ids) {
        var source = [];
        if (typeof ids === 'number') {
          source.push(ids);
        } else if (typeof ids === 'string' && ids === 'selected') {
          source = source.concat(Object.keys(scope.activeNodes));
        } else {
          throw 'I don\'t know what you are trying to do!';
        }
        // Modal configuration
        var modalConfiguration = {
          templateUrl: SETTINGS.viewsPath + '/modals/context-browser-node-linker.html',
          backdrop: true,
          scope: scope.$new(),
          resolve: {
            source: function () {
              return source;
            }
          },
          controller: function ($scope, $modalInstance, source, target) {
            $scope.items = [
              { id: 1, name: 'hasVersion' },
              { id: 2, name: 'hasPart' },
              { id: 3, name: 'hasFormat' },
              { id: 4, name: 'hasVersion' },
              { id: 5, name: 'isReferencedBy' },
              { id: 6, name: 'isReplacedBy' },
              { id: 7, name: 'isRequiredBy' },
              { id: 8, name: 'conformsTo' }
            ];
            $scope.source = source;
            $scope.target = target;
            $scope.selected = $scope.items[0];
            // This doesn't feel right, but the <select/> has its own scope and
            // it doesn't inherit ^ $scope.selected, why?
            $scope.change = function (selection) {
              $scope.selected = selection;
            };
            $scope.save = function () {
              $modalInstance.close($scope.selected);
            };
            $scope.cancel = function () {
              $modalInstance.dismiss('Cancel');
            };
          }
        };
        // Prompt the user
        scope.cb.promptNodeSelection({
          exclude: source,
          action: function (target) {
            modalConfiguration.resolve.target = function () {
              return target;
            };
            var modal = $modal.open(modalConfiguration);
            modal.result.then(function (type) {
              scope.cb.createAssociativeRelationship(source, target, type);
            });
          }
        });
      };

      scope.crudRelatedTechnologies = function (id) {
        ModalLinkSupportingTechnologyService.open(id).result.then(function () {
          scope.pull();
        });
      };

    }
  };
};
