(function () {

  'use strict';

  angular.module('drmc.controllers').controller('ContextBrowserCtrl', function ($scope, $rootScope, $element, $document, $modal, ModalAssociativeRelationshipService, ModalDigitalObjectViewerService, InformationObjectService, FullscreenService) {

    // Aliases (not needed, just avoiding the refactor now)
    var scope = $scope;
    var element = $element;

    // Our directive has an isolated model, so we need to import the following
    // reference manually. Is there a better solution?
    scope.viewsPath = $rootScope.viewsPath;


    /**
     * cbd initialization
     */

    var container = element.find('.svg-container');
    var cb = new ContextBrowser(container);
    scope.cb = cb; // So I can share it with the link function...


    /**
     * Fetcher
     */

    var firstPull = true;
    scope.pull = function () {
      var self = this;
      InformationObjectService.getTree(scope.id)
        .then(function (tree) {
          // Empty container
          container.empty();

          // Init context browser
          cb.init(tree, function (u) {
            var node = self.cb.graph.node(u);
            // Hide AIPs
            if (node.level === 'aip') {
              node.hidden = true;
            }
          });

          // Define ranking direction
          scope.rankDir = cb.renderer.rankDir;

          if (firstPull) {
            firstPull = false;
            cb.selectRootNode();
            scope.selectNode(scope.id);
          } else {
            scope.unselectAll();
          }

        }, function (reason) {
          console.error('Error loading tree:', reason);
        });
    };

    scope.$watch('id', function (value) {
      if (value.length < 1) {
        return;
      }
      scope.pull();
    });

    scope.$on('reload', function () {
      scope.pull();
    });


    /**
     * cbd events
     */

    cb.events.on('pin-node', function (attrs) {
      scope.$apply(function (scope) {
        scope.selectNode(attrs.id);
      });
    });

    cb.events.on('unpin-node', function (attrs) {
      scope.$apply(function () {
        scope.unselectNode(attrs.id);
      });
    });

    // TODO: this is not working, see ContextBrowser.prototype.clickSVG
    cb.events.on('click-background', function () {
      scope.$apply(function () {
        scope.unselectAll();
      });
    });

    cb.events.on('click-path', function (attrs) {
      var type;
      try {
        type = attrs.edge.type;
      } catch (e) {}
      if (!angular.isDefined(type)) {
        return false;
      }
      if (type === 'associative') {
        ModalAssociativeRelationshipService.edit(attrs.edge.relationId).result.then(function (response) {
          if (response.action === 'deleted') {
            scope.cb.graph.delEdge(attrs.id);
            scope.cb.draw();
          } else if (response.action === 'updated') {
            var edge = scope.cb.graph.edge(attrs.id);
            edge.label = response.type.name;
            scope.cb.draw();
          }
        });
      }
    });


    /**
     * cbd rank directions
     */

    scope.rankingDirections = {
      'LR': 'Left-to-right',
      'RL': 'Right-to-left',
      'TB': 'Top-to-bottom',
      'BT': 'Bottom-to-top'
    };

    scope.changeRankingDirection = function (rankDir) {
      cb.changeRankingDirection(rankDir);
      scope.rankDir = rankDir;
    };


    /**
     * cbd misc
     */

    scope.center = function () {
      cb.center();
    };


    /**
     * Node generic actions
     */

    scope.collapseAll = function () {
      cb.graph.predecessors(scope.id).forEach(function (u) {
        cb.collapse(u, true);
      });
    };


    /**
     * Selection
     */

    // TODO: Should I stop using a dictionary? The idea was to use the key to hold
    // the Id, but js won't let me store integers just strings, which is
    // unfortunate for direct access.
    scope.activeNodes = {};

    scope.hasNodeSelected = function () {
      return Object.keys(scope.activeNodes).length === 1;
    };

    scope.hasNodesSelected = function () {
      return Object.keys(scope.activeNodes).length > 1;
    };

    scope.getNumberOfSelectedNodes = function () {
      return Object.keys(scope.activeNodes).length;
    };

    scope.unselectNode = function (id) {
      delete scope.currentNode;
      delete scope.activeNodes[id];
    };

    scope.unselectAll = function () {
      delete scope.currentNode;
      scope.activeNodes = {};
      cb.unselectAll();
    };

    scope.cancelBulkEdit = function () {
      scope.unselectAll();
    };


    /**
     * Legend
     */

    scope.showLegend = false;
    scope.toggleLegend = function () {
      scope.showLegend = !scope.showLegend;
    };


    /**
     * Fullscreen mode
     */

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
      scope.$apply(function () {
        if (args.type === 'enter') {
          scope.isFullscreen = true;
        } else {
          scope.isFullscreen = false;
        }
      });
      cb.center();
    });


    /**
     * Maximized mode
     */

    scope.isMaximized = false;

    scope.toggleMaximizedMode = function () {
      scope.isMaximized = !scope.isMaximized;
    };

    scope.$watch('isMaximized', function (oldValue, newValue) {
      if (oldValue !== newValue) {
        cb.center();
      }
    });


    /**
     * Relationships
     */

    scope.showRelationships = true;
    scope.hideRelationships = function () {
      scope.showRelationships = !scope.showRelationships;
      if (scope.showRelationships) {
        cb.showRelationships();
      } else {
        cb.hideRelationships();
      }
    };


    /**
     * AIPs
     */

    // Visibility of AIP nodes defaults to false
    scope.showAips = false;
    scope.toggleAips = function () {
      scope.showAips = !scope.showAips;
      scope.cb.toggleNodesVisibility(function (node) {
        return node.level === 'aip';
      }, scope.showAips);
    };


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
     * Node action
     */

    scope.selectNode = function (id) {
      scope.currentNode = scope.activeNodes[id] = { id: id };
      // Fetch information from the server
      InformationObjectService.getById(id).then(function (response) {
        scope.currentNode.data = response.data;
        // Invoke corresponding function injected in the scope
        scope._selectNode();
        // Retrieve a list of files or digital objects
        // TODO: pager?
        InformationObjectService.getDigitalObjects(id, false, { limit: 100 }).then(function (response) {
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

    scope.linkNode = function (id) {
      // Prompt the user
      scope.cb.promptNodeSelection({
        exclude: [id],
        action: function (target) {
          var s = {
            id: id,
            label: scope.cb.graph.node(id).label
          };
          var t = {
            id: target,
            label: scope.cb.graph.node(target).label
          };
          ModalAssociativeRelationshipService.create(s, t).result.then(function (response) {
            scope.cb.createAssociativeRelationship(response.id, s, t, response.type);
          }, function () {
            scope.cb.cancelNodeSelection();
          });
        }
      });
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
      // Build a list of descendants
      var exclusionList = [];
      source.forEach(function (v) {
        var descendants = scope.cb.graph.descendants(v, { onlyId: true, andSelf: true });
        for (var i = 0; i < descendants.length; i++) {
          // Remember that we are dealing with strings here (see activeNodes) :(
          var nv = String(descendants[i]);
          // Avoid to add the same id twice
          if (exclusionList.indexOf(nv) === -1) {
            exclusionList.push(nv);
          }
        }
      });
      // Prompt
      scope.cb.promptNodeSelection({
        exclude: exclusionList,
        action: function (target) {
          InformationObjectService.move(source, target).then(function () {
            scope.cb.moveNodes(source, target);
            scope.selectNode(target);
          }, function () {
            scope.cb.cancelNodeSelection();
          });
        }
      });
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

      if (candidates.length > 1) {
        throw 'Not supported yet!';
      }

      InformationObjectService.delete(candidates[0]).then(function () {
        scope.cb.deleteNodes(candidates);
        scope.activeNodes = {};
      }, function () {
        throw 'Error deleting ' + candidates[0];
      });
    };


    /**
     * Keyboard shortcuts
     * TODO: I should destroy this when $destroy is triggered and limit its focus
     */

    var onKeyUp = function (event) {
      // Escape shortcut
      if (event.which === 27 && scope.isMaximized) {
        console.log('escape');
        scope.$apply(function () {
          scope.toggleMaximizedMode();
        });
      // Maximized mode (ctrl+f)
      } else if (event.which === 70 && event.ctrlKey && !scope.isFullscreen) {
        scope.$apply(function () {
          scope.toggleMaximizedMode();
        });
      }
    };

    $document.on('keyup', onKeyUp);


    /**
     * Destroy: remove DOM events
     */

    scope.$on('$destroy', function () {
      $document.off('keyup', onKeyUp);
    });

  });

})();
