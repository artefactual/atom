'use strict';

angular.module('momaApp.directives')
  .directive('plumbGraph', function() {
    return {
      restrict: 'C',
      scope: {
        collection: '='
      },
      link: function(scope, element) {
        scope.plumb = new Plumb(element, scope);
        scope.plumb.initialize();
        scope.plumb.draw();
      }
    };
  });
