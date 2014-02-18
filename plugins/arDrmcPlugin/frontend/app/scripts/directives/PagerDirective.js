'use strict';

module.exports = function (ATOM_CONFIG) {
  return {
    restrict: 'E',
    templateUrl: ATOM_CONFIG.viewsPath + '/partials/pager.html',
    replace: true,
    scope: {
      itemsPerPage: '@',
      totalItems: '@',
      page: '=' // Two-way binding!
    },
    link: function (scope) {
      scope.$watch('totalItems', function () {
        scope.numberOfPages = Math.ceil(scope.totalItems / scope.itemsPerPage);
      });

      scope.showPrev = true;
      scope.showNext = true;

      scope.$watch('page', function () {
        console.log('PageDirective', 'Page has changed', scope.page);
      });

      scope.next = function () {
        if (scope.page === scope.numberOfPages) {
          return false;
        }
        scope.page++;
      };

      scope.prev = function () {
        if (scope.page === 1) {
          return false;
        }
        scope.page--;
      };
    }
  };
};
