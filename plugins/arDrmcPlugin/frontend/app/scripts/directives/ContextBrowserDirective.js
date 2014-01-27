'use strict';

module.exports = function (ATOM_CONFIG) {
  return {
    restrict: 'E',
    scope: {
      collection: '='
    },
    templateUrl: ATOM_CONFIG.viewsPath + '/partials/context-browser.html',
    replace: true,
    link: function (scope, element) {

      // This layer will be the closest HTML container of the SVG
      var container = element.find('.container');

      // Import cbd, the context browser module
      // I can't remember what is the 'd' for :P
      new (require('../lib/cbd'))(container, scope.collection);



    }
  };
};
