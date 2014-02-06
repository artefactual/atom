'use strict';

var ContextBrowser = require('../lib/cbd');

module.exports = function (ATOM_CONFIG, InformationObjectService, FullscreenService) {
  return {
    restrict: 'E',
    templateUrl: ATOM_CONFIG.viewsPath + '/partials/context-browser.html',
    scope: {
      resource: '@resource'
    },
    replace: true,
    link: function (scope, element) {
      // This layer will be the closest HTML container of the SVG
      var container = element.find('.svg-container');

      var cb = window.cb = new ContextBrowser(container);

      var firstSelection;

      cb.events.on('pin-node', function (attrs) {
        scope.$apply(function () {
          if (typeof firstSelection === 'undefined') {
            firstSelection = attrs.id;
          }
          scope.activeNodes[attrs.id] = attrs;
          InformationObjectService.getWork(attrs.id).then(function (work) {
              scope.activeNodes[attrs.id].data = work;
            });
        });
      });

      cb.events.on('unpin-node', function (attrs) {
        scope.$apply(function () {
          if (attrs.id === firstSelection) {
            scope.activeNodes = {};
            firstSelection = undefined;
          } else {
            delete scope.activeNodes[attrs.id];
          }
        });
      });

      // Selected nodes
      scope.activeNodes = {};
      scope.hasActiveNodes = function () {
        return Object.keys(scope.activeNodes).length > 1;
      };
      scope.hasOneNodeActive = function () {
        if (Object.keys(scope.activeNodes).length === 1)
        {
          scope.activeNode = scope.activeNodes[Object.keys(scope.activeNodes)[0]];

          return true;
        }

        return false;
      };

      // Fetch data from the server
      InformationObjectService.getTree(scope.resource)
        .then(function (tree) {
          cb.init(tree);
        }, function (reason) {
          console.error('Error loading tree:', reason);
        });

      // Maximize/minimize. Center the graph within the loop.
      scope.isMaximized = false;
      scope.toggleMaximizedMode = function () {
        scope.isMaximized = !scope.isMaximized;
      };
      scope.$watch('isMaximized', function (oldValue, newValue) {
        if (oldValue !== newValue) {
          cb.center();
        }
      });

      // Fullscreen mode
      scope.isFullscreen = false;
      scope.toggleFullscreenMode = function () {
        if (scope.isFullscreen) {
          FullscreenService.cancel();
        } else {
          FullscreenService.enable(element.get(0));
        }
        scope.isFullscreen = !scope.isFullscreen;
        cb.center();
      };
      scope.$on('fullscreenchange', function (event, args) {
        if (args.type === 'enter') {
          scope.isFullscreen = true;
        } else {
          scope.isFullscreen = false;
        }
        cb.center();
      });

      // Hide relationships
      scope.areRelationshipsHidden = false;
      scope.hideRelationships = function () {
        scope.areRelationshipsHidden = !scope.areRelationshipsHidden;
        if (scope.areRelationshipsHidden) {
          cb.hideRelationships();
        } else {
          cb.showRelationships();
        }
      };

      // Add child node
      scope.addChildNode = function (parentId) {
        var label = prompt('Insert label');
        var id = Math.random() * 100;
        if (label.length === 0) {
          return;
        }
        cb.addNode(id, label, 'description', parentId);
      };

      // Delete node
      scope.deleteNode = function (id) {
        cb.deleteNode(id);
        scope.activeNodes = {};
      };
    }
  };
};
