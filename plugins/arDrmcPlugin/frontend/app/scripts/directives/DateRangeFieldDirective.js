'use strict';

module.exports = function () {
  return {
    restrict: 'C',
    require: 'ngModel',
    link: function (scope, element, attrs, ngModelCtrl) {

      // js doesn't allow to define properties of a
      // nested object unless you define it first?
      scope.criteria = {};
      scope.criteria.range = {};
      scope.criteria.range.from = undefined;
      scope.criteria.range.to = undefined;

      // get value when changed
      scope.$watch('criteria.range.from', function (newVal) {
        if (newVal) {
          ngModelCtrl.$setViewValue({
            from: newVal,
            to: scope.criteria.range.to
          });
        }
      });

      scope.$watch('criteria.range.to', function (newVal) {
        if (newVal) {
          ngModelCtrl.$setViewValue({
            to: newVal,
            from: scope.criteria.range.from
          });
        }
      });
    }
  };
};
