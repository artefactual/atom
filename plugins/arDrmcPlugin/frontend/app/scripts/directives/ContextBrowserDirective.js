'use strict';

var ContextBrowser = require('../lib/cbd');

module.exports = function ($document, ATOM_CONFIG, InformationObjectService, FullscreenService) {
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

      // There may be many nodes selected, but firstSelection should be pointing
      // to the first one so we can do bulk edits over it
      scope.firstSelection = undefined;
      scope.lastSelection = undefined;
      scope.activeNodes = {};

      cb.events.on('pin-node', function (attrs) {
        scope.$apply(function () {
          if (typeof firstSelection === 'undefined') {
            scope.firstSelection = attrs.id;
          }

          scope.lastSelection = scope.activeNodes[attrs.id] = attrs;

          InformationObjectService.getWork(attrs.id).then(function (work) {
              scope.activeNodes[attrs.id].data = work;
            });
        });
      });

      cb.events.on('unpin-node', function (attrs) {
        scope.$apply(function () {
          delete scope.activeNodes[attrs.id];
        });
      });

      // Selected nodes
      scope.hasNodeSelected = function () {
        return scope.lastSelection !== undefined;
      };
      scope.hasNodesSelected = function () {
        return Object.keys(scope.activeNodes).length > 1;
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

      // Exit maximized mode using ESC key
      $document.on('keyup', function (event) {
        if (event.which === 27 && scope.isMaximized) {
          scope.$apply(function () {
            scope.toggleMaximizedMode();
          });
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

      scope.addChildNode = function (parentId) {
        var label = prompt('Insert label');
        var id = Math.random() * 100;
        if (label.length === 0) {
          return;
        }
        cb.addNode(id, label, 'description', parentId);
      };

      scope.deleteNodes = function (ids) {
        var candidates = [];
        if (typeof ids === 'number') {
          candidates.push(ids);
        } else if (typeof ids === 'string' && ids === 'selected') {
          candidates = candidates.concat(Object.keys(scope.activeNodes));
        } else {
          throw 'I don\'t know what you are trying to do!';
        }
        cb.deleteNodes(candidates);
        scope.activeNodes = {};
      };

      scope.moveNodes = function (ids) {
        var source = [];
        if (typeof ids === 'number') {
          source.push(ids);
        } else if (typeof ids === 'string' && ids === 'selected') {
          source = source.concat(Object.keys(scope.activeNodes));
        } else {
          throw 'I don\'t know what you are trying to do!';
        }
        cb.promptNodeSelection({
          exclude: source,
          action: function (target) {
            cb.moveNodes(source, target);
          }
        });
      };
    }
  };
};
