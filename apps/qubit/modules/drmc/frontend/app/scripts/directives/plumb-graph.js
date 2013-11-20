'use strict';

angular.module('momaApp.directives', ['jsPlumb'])
  .directive('plumbGraph', function(jsPlumbService) {
    return {
      restrict: 'AE',
      scope: {
        collection: '=',
        relations: '='
      },
      link: function(scope, element, attrs) {
        jsPlumbService.jsPlumb().then(function(jsPlumb) {

          scope.plumb = new Plumb(element, scope);
          scope.plumb.initialize();

          scope.$watch('collection', function(newValue, oldValue) {

            console.log("WATCHED collection", newValue, oldValue);

            if (!scope.plumb)
            {
              return;
            }

            console.log(newValue, oldValue);
            scope.plumb.draw();

          });

        });
      }
    };
  });
