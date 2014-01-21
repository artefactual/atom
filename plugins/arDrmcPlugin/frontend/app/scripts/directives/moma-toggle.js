'use strict';

angular.module('momaApp.directives')
  .directive('momaToggle', function() {

      //This directive includes a hide/show with animation
      //To use, put ng-click="toggle()" on the link/button,etc
      //Use attribute/element name with moma-toggle="isVisible"

      return {
        restrict: 'AE',
        $scope: true,
        link: function($scope, element, attributes) {

        //watched expression
        var expression = attributes.momaToggle;

        if (! $scope.$eval( expression ) ) {
          element.hide();
        };

        $scope.$watch(expression, function( newVal, oldVal ) {

            console.log("WATCHED exp", newVal, oldVal);

           if (newVal === oldVal) {
                return;
                }


            //show hidden element
            if( newVal ) {
              element
                .stop( true, true )
                .slideDown ();
            } else {
              element
                .stop( true, true )
                .slideUp();
            }
          }); //watch
      }
  }
});
