'use strict';

angular.module('momaApp.directives')
  .directive('modalVideo1', function() {
    return {
        restrict: 'AE',
        template: '<video controls ng-src="{{videoUrl1}}" ></video>'
        }
    })
  .directive('modalVideo2', function() {
    return {
        restrict: 'AE',
        template: '<video controls ng-src="{{videoUrl2}}" ></video>'
        }
    });
