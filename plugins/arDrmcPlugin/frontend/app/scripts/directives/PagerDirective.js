'use strict';

/**
 * TODO:
 *  - After pull.success, current page may be out of range. Reset if it happens.
 *  - We should be using angular-ui Pagination directive,
      specially because on-select-page is a much better approach!
 */

module.exports = function ($compile, SETTINGS) {
  return {
    restrict: 'E',
    templateUrl: SETTINGS.viewsPath + '/partials/pager.html',
    replace: true,
    scope: {
      itemsPerPage: '@',
      page: '='
    },
    link: function (scope, element) {
      scope.$on('pull.success', function (_, count) {
        scope.totalItems = count;
        scope.numberOfPages = Math.ceil(scope.totalItems / scope.itemsPerPage);
        scope.showPrev = scope.page > 1;
        scope.showNext = scope.page < scope.numberOfPages;

        draw();
      });

      scope.haveToPaginate = function () {
        if (typeof scope.numberOfPages === 'undefined') {
          return false;
        }
        return scope.numberOfPages > 1;
      };

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

      scope.go = function (page) {
        scope.page = page;
      };

      scope.getFirstIndice = function () {
        if (scope.page === 0) {
          return 1;
        } else {
          return (scope.page - 1) * scope.itemsPerPage + 1;
        }
      };

      scope.getLastIndice = function () {
        if (scope.page === 0) {
          return scope.totalItems;
        } else {
          if (scope.page * scope.itemsPerPage >= scope.totalItems) {
            return scope.totalItems;
          } else {
            return scope.page * scope.itemsPerPage;
          }
        }
      };

      /**
       * Draws pager in the DOM
       */
      var draw = function () {
        var placeholder = element.find('.pagination');
        var el = jQuery('<ul></ul>');
        var last = scope.numberOfPages;
        var total = last;
        var max = 7;

        var printLink = function (page) {
          if (page === scope.page) {
            el.append('<li class="active"><span>' + page + '</span></li>');
          } else {
            el.append('<li><a href ng-click="go(' + page + ')">' + page + '</a></li>');
          }
        };

        var pageNumbers = getLinks(max);
        pageNumbers.forEach(function (page, index) {
          if (index === 0) {
            printLink(1);
            if (page === 1) {
              return;
            }
            el.append('<li class="dots"><span>...</span></li>');
          }
          printLink(page);
        });

        // Add link to last page if the last page number shown isn't the last page
        var lastPageNumberShown = pageNumbers[pageNumbers.length - 1];
        if (lastPageNumberShown < total) {
          el.append('<li class="dots"><span>...</span></li>');
          el.append('<li><a href ng-click="go(' + last + ')">' + last + '</a></li>');
        }

        // Add previous and next buttons
        el.prepend('<li class="previous" ng-if="showPrev"><a href ng-click="prev()">&laquo; Previous</a></li>');
        el.append('<li class="next" ng-if="showNext"><a href ng-click="next()">Next &raquo;</a></li>');

        // Compile and render
        var compiled = $compile(el);
        placeholder.html(el);
        compiled(scope);
      };

      /**
       * Returns an array of page numbers to use in pagination links.
       *
       * @param {number} maxPages - Maximum number of pages
       * @return array
       */
      var getLinks = function (maxPages) {
        var links = [];
        var tmp = scope.page - Math.floor(maxPages / 2);
        var check = scope.numberOfPages - maxPages + 1;
        var limit = check > 0 ? check : 1;
        var begin = tmp > 0 ? (tmp > limit ? limit : tmp) : 1;

        var i = begin;
        while (i < begin + maxPages && i <= scope.numberOfPages) {
          links.push(i++);
        }

        return links;
      };
    }
  };
};
