'use strict';

angular.module('momaApp.directives')
  .directive('plumbGraph', function() {
    return {
      restrict: 'AE',
      scope: {
        collection: '=',
        relations: '='
      },
      link: function(scope, element, attrs) {
        scope.plumb = new Plumb(element, scope);
        scope.plumb.initialize();
      }
    };
  });
