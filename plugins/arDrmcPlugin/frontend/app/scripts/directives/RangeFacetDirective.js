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
        scope.from = from;
        scope.to = to;
      };

      scope.isSelected = function (from, to) {
        return scope.from === from && scope.to === to;
      };

      scope.getLabel = function (from, to) {
        if (scope.label === 'Ingested date') {
          return scope.callback({arg1: 'ingestedDate', arg2: from, arg3: to});
        }
        return scope.callback({arg1: from, arg2: to});
      };
    }
  };
};
