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

          scope.plumb = new Plumb(element, {});
          scope.plumb.initialize();

          scope.$watch('collection', function() {
            if (!scope.plumb)
            {
              return;
            }

            scope.plumb.redraw({
              collection: scope.collection,
              relations: scope.relations});
          });

        });
      }
    };
  });
