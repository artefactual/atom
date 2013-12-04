'use strict';

angular.module('momaApp.directives')
  .directive('modalVideo', function() {
        // var videoUrl = '{{ atomGlobals.relativeUrlRoot }}/apps/qubit/modules/drmc/frontend/assets/play-dead-channel-1/play-dead-channel-1.mp4';

    return {
        restrict: 'AE',
        template: '<video controls ng-src="{{videoUrl}}" ></video>'
    }
});
