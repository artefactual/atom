'use strict';

angular.module('momaApp.directives')
  .directive('arContextBrowser', function() {
    return {
      restrict: 'E',
      scope: {
        collection: '='
      },
      link: function() {

      }
    };
  });
