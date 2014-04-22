'use strict';

module.exports = function (SETTINGS) {
  return {
    restrict: 'E',
    templateUrl: SETTINGS.viewsPath + '/partials/range-facet.html',
    replace: true,
    scope: {
      type: '@',
      label: '@',
      facet: '=',
      from: '=',
      to: '=',
      callback: '&'
    },
    link: function (scope) {
      scope.collapsed = false;

      scope.toggle = function () {
        scope.collapsed = !scope.collapsed;
      };

      scope.select = function (from, to) {
        if (scope.from === from && scope.to === to) {
          scope.from = undefined;
          scope.to = undefined;
          return;
        }
        scope.from = from;
        scope.to = to;
      };

      scope.isSelected = function (from, to) {
        return scope.from === from && scope.to === to;
      };

      scope.getLabel = function (from, to) {
        if (scope.label === 'Date ingested' || scope.label === 'Date materials ingested') {
          return scope.callback({arg1: 'dateIngested', arg2: from, arg3: to});
        }
        if (scope.label === 'Date collected') {
          return scope.callback({arg1: 'dateCollected', arg2: from, arg3: to});
        }
        if (scope.label === 'Date created') {
          return scope.callback({arg1: 'dateCreated', arg2: from, arg3: to});
        }
        return scope.callback({arg1: from, arg2: to});
      };
    }
  };
};
