'use strict';

module.exports = function ($document, SETTINGS) {
  return {
    restrict: 'E',
    scope: false,
    replace: true,
    templateUrl: SETTINGS.viewsPath + '/partials/multiselect.input.html',
    link: function (scope, element) {

      // TODO: delete this directive and compile multiselect-popup
      // from multiselect directive using ng-include or compile
      // http://goo.gl/HJ5rVq

      scope.isVisible = false;

      scope.focus = function focus () {
        var searchBox = element.find('input')[0];
        searchBox.focus();
      };
    }
  };
};
