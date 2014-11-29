(function () {

  'use strict';

  angular.module('drmc.filters').filter('EmptyFilter', function () {

    // This allows for addition of dom elements around data, and returns empty if
    // field contains no data
    return function (x) {
      if (!(x === undefined || x === null)) {
        return '(' + x + ')';
      } else {
        return;
      }
    };

  });

})();
