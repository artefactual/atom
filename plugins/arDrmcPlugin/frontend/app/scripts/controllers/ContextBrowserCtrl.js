'use strict';

var ContextBrowser = require('../lib/cbd');

module.exports = function ($scope, $element, $document, InformationObjectService, FullscreenService) {

  // Aliases (not needed, just avoiding the refactor now)
  var scope = $scope;
  var element = $element;


  /**
   * cbd initialization
   */

  var container = element.find('.svg-container');
  var cb = new ContextBrowser(container);
  scope.cb = cb; // So I can share it with the link function...


  /**
   * Fetcher
   */

  scope.pull = function () {
    InformationObjectService.getTree(scope.id)
      .then(function (response) {
        container.empty();
        cb.init(response.data);
        scope.rankDir = cb.renderer.rankDir;
        scope.unselectAll();
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

  cb.events.on('click-background', function () {
    scope.$apply(function () {
      scope.unselectAll();
    });
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
    scope.cb.center();
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
   * Dublin Core metadata
   */

  scope.dcCollapsed = true;

  scope.dcFields = [
    'identifier',
    'title',
    'description',
    'names',
    'dates',
    'types',
    'format',
    'source',
    'rights'
  ];

  scope.hasDcField = function (field) {
    return typeof scope.currentNode.data[field] !== 'undefined';
  };

  // TODO: this should be a filter
  scope.renderMetadataValue = function (value) {
    if (angular.isArray(value)) {
      if (value.length && angular.isObject(value[0])) {
        var items = [];
        for (var i in value) {
          items.push(scope.renderMetadataValue(value[i]));
        }
        return items.join(' | ');
      }
      return value.join(', ');
    } else if (angular.isString(value)) {
      return value;
    } else {
      return String(value);
    }
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

  scope.areRelationshipsHidden = false;

  scope.hideRelationships = function () {
    scope.areRelationshipsHidden = !scope.areRelationshipsHidden;
    if (scope.areRelationshipsHidden) {
      cb.hideRelationships();
    } else {
      cb.showRelationships();
    }
  };


  /**
   * Node action
   */

  scope.moveNodes = function (ids) {
    var source = [];
    if (typeof ids === 'number') {
      source.push(ids);
    } else if (typeof ids === 'string' && ids === 'selected') {
      source = source.concat(Object.keys(scope.activeNodes));
    } else {
      throw 'I don\'t know what you are trying to do!';
    }
    var exclusionList = scope.cb.graph.descendants(ids, { onlyId: true, andSelf: true });
    scope.cb.promptNodeSelection({
      exclude: exclusionList,
      action: function (target) {
        InformationObjectService.move(source, target).then(function () {
          scope.cb.moveNodes(source, target);
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

};
