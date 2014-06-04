'use strict';

module.exports = function () {
  return {
    restrict: 'C',
    require: 'ngModel',
    link: function (scope, element, attrs, ngModelCtrl) {

      // Reference to DOM elements
      var $from = element.find('.from');
      var $to = element.find('.to');

      // DOM listener
      var listener = function () {
        scope.$apply (function () {
          ngModelCtrl.$setViewValue({
            from: $from.val(),
            to:   $to.val()
          });
        });
      };

      $from.on('change', listener);
      $to.on('change', listener);

      // Update the view when the model changes
      ngModelCtrl.$render = function () {
        if (angular.isUndefined(ngModelCtrl.$viewValue)) {
          ngModelCtrl.$viewValue = {};
        }
        $from.get(0).valueAsDate = new Date(ngModelCtrl.$viewValue.from);
        $to.get(0).valueAsDate = new Date(ngModelCtrl.$viewValue.to);
      };
    }
  };
};
