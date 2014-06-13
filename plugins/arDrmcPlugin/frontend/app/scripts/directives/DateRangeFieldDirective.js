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
      $to.on('blur', listener);

      // Update the view when the model changes
      ngModelCtrl.$render = function () {
        if (angular.isUndefined(ngModelCtrl.$viewValue)) {
          ngModelCtrl.$viewValue = {};
        }
        $from.get(0).valueAsDate = new Date(ngModelCtrl.$viewValue.from);
        $to.get(0).valueAsDate = new Date(ngModelCtrl.$viewValue.to);
      };

      var validate = function (value) {
        ngModelCtrl.$setValidity('range', false);
        if (!angular.isObject(value) || value === {}) {
          ngModelCtrl.$setValidity('range', false);
          return undefined;
        }
        if (angular.isUndefined(value.from) || angular.isUndefined(value.to)) {
          ngModelCtrl.$setValidity('range', false);
          return undefined;
        }
        if (value.to === '' || value.from === '') {
          ngModelCtrl.$setValidity('range', false);
          ngModelCtrl.$setPristine('range', true);
          return undefined;
        }
        var from = $from.get(0).valueAsDate;
        var to = $to.get(0).valueAsDate;
        if (to <= from) {
          ngModelCtrl.$setValidity('range', false);
          return undefined;
        }
        ngModelCtrl.$setValidity('range', true);
        return value;
      };

      var convertDate = function (value) {
        if (ngModelCtrl.$invalid) {
          return value;
        }
        var from = $from.get(0).valueAsDate;
        var to = $to.get(0).valueAsDate;
        return {
          from: from.toISOString(),
          to: to.toISOString()
        };
      };

      ngModelCtrl.$formatters.push(validate);
      ngModelCtrl.$parsers.push(validate);
      ngModelCtrl.$parsers.push(convertDate);
    }
  };
};
