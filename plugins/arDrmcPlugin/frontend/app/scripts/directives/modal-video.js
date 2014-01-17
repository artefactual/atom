'use strict';

angular.module('momaApp.directives')
  .directive('modalVideo', function() {
    return {
      restrict: 'AE',
      template: '<video controls src="{{ videoUrl }}"></video>'
    };
  });
