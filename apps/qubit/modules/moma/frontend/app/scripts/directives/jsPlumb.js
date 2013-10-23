'use strict';

angular.module('momaApp.directives', ['jsPlumb'])
  .directive('plumbGraph', function(jsPlumbService) {
    return {
      restrict: 'A',
      scope: {
        collection: '='
      },
      link: function(scope, element, attrs) {
        jsPlumbService.jsPlumb().then(function(jsPlumb) {

          var configuration = {
          };

          scope.plumb = new Plumb(element[0], configuration);
          scope.plumb.render();
          scope.plumb.redraw(scope.collection);

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
