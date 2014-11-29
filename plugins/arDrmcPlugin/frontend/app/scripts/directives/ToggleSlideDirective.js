(function () {

  'use strict';

  angular.module('drmc.directives').directive('arToggleSlide', function () {

    return {
      restrict: 'A',
      link: function ($scope, el, attr) {
        var exp = attr.arToggleSlide;
        var dur = 500;

        // Initial state
        if (!$scope.$eval(exp)) {
          el.hide();
        }

        // Watch for changes
        $scope.$watch(
          exp,
          function (newVal, oldVal) {
            // Ignores (returns) intial state
            if (newVal === oldVal) {
              return;
            }

            // Now show!
            if (newVal) {
              el.stop(true, true).slideDown(dur);
            } else {
            // Now hide!
              el.stop(true, true).slideUp(dur);
            }
          });
      }
    };

  });

})();
