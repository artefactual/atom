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

      console.log('The facet directive can see your criteria!', scope.criteria);

      scope.toggle = function () {
        scope.collapsed = !scope.collapsed;
        console.log(scope.collapsed);
      };

      scope.select = function (key) {
        var term = scope.terms[key];
        if (typeof term.active === 'undefined' || term.active == false) {
          term.active = true;
        } else {
          term.active = !term.active;
        }

        scope.criteria[scope.field] = scope.terms[key].term;
      };
    }
  };
};
