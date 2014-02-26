'use strict';

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

      scope.toggle = function () {
        scope.collapsed = !scope.collapsed;
      };

      scope.select = function (id) {
        // Create array if undefined
        if (typeof scope.criteria[scope.field] === 'undefined') {
          scope.criteria[scope.field] = [id];
          return;
        }
        // Update scope.criteria.[scope.field]
        var index = jQuery.inArray(id, scope.criteria[scope.field]);
        if (index === -1) {
          scope.criteria[scope.field].push(id);
        } else {
          scope.criteria[scope.field].splice(index, 1);
        }
      };

      scope.isSelected = function (id) {
        return jQuery.inArray(id, scope.criteria[scope.field]) !== -1;
      };
    }
  };
};
