'use strict';

/**
 * TODO:
 *  - In criteria[field] should allow multiple values (array)
 *  - Selected values should be controlled by its ID
 */

module.exports = function (ATOM_CONFIG) {
  return {
    restrict: 'E',
    templateUrl: ATOM_CONFIG.viewsPath + '/partials/facet.html',
    replace: true,
    scope: {
      label: '@',
      field: '@',
      terms: '=',
      criteria: '=',
    },
    link: function (scope) {
      scope.collapsed = false;
      scope.selections = [];

      scope.toggle = function () {
        scope.collapsed = !scope.collapsed;
      };

      scope.$watch('criteria.' + scope.field, function (newValue, oldValue) {
        if (!angular.equals(newValue, oldValue)) {
          scope.selections = [newValue];
        }
      }, true);

      /**
       * Updates selections and criteria
       */
      scope.select = function (id) {
        var index = jQuery.inArray(id, scope.selections);
        if (index === -1) {
          scope.criteria[scope.field] = id; // scope.terms[id].term;
        } else {
          delete scope.criteria[scope.field];
        }
      };

      scope.isSelected = function (id) {
        return jQuery.inArray(id, scope.selections) !== -1;
      };
    }
  };
};
