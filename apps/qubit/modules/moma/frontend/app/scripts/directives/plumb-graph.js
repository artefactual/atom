'use strict';

angular.module('momaApp.directives', ['jsPlumb'])
  .directive('plumbGraph', function(jsPlumbService) {
    return {
      restrict: 'AE',
      scope: {
        collection: '='
      },
      link: function(scope, element, attrs) {
        jsPlumbService.jsPlumb().then(function(jsPlumb) {

          console.log("MUU");
          console.log("WREBBIT");

          scope.plumb = new Plumb(element, {});
          scope.plumb.initialize();

          scope.$watch('collection', function() {
            if (scope.plumb)
            {
              scope.plumb.redraw(scope.collection);
            }
          });

        });
      }
    };
  });
