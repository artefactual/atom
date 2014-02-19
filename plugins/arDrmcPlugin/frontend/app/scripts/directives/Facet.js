'use strict';

module.exports = function (ATOM_CONFIG) {
  return {
    restrict: 'E',
    templateUrl: ATOM_CONFIG.viewsPath + '/partials/facet.html',
    replace: true,
    scope: {
      label: '@',
      criteria: '='
    },
    link: function (scope) {

      scope.items = {
        1: { label: 'Element A', count: 100 },
        2: { label: 'Element B', count: 25 },
        3: { label: 'Element C', count: 10 },
        4: { label: 'Element D', count: 1 }
      };

      scope.collapsed = false;

      console.log('The facet directive can see your criteria!', scope.criteria);

      scope.toggle = function () {
        scope.collapsed = !scope.collapsed;
        console.log(scope.collapsed);
      };

      scope.select = function (key) {
        console.log(key);
        var item = scope.items[key];
        if (typeof item.active === 'undefined') {
          item.active = true;
        } else {
          item.active = !item.active;
        }
      };

    }
  };
};
