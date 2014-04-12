'use strict';

module.exports = function ($parse, $document, $compile, ParseInputService) {
  return {
    // TODO: make isolate scope that passes id, data and uncheckAll function
    restrict: 'AE',
    require: 'ngModel',
    scope: {
      uncheckAll: '&'
    },
    link: function (scope, element, attrs, ngModelCtrl) {
      // ng-model doesn't update the view directly, listen for user input or interact with the dom

      var exp = attrs.options;
      var parsedResult = ParseInputService.parse(exp);
      var isMultiple = attrs.multiple ? true : false;
      var required = false;
      var changeHandler = attrs.change || angular.noop;

      scope.items = [];
      scope.header = 'Select';
      scope.multiple = isMultiple;
      scope.disabled = false;

      var popUpEl = angular.element('<ar-multiselect-popup></ar-multiselect-popup>');

      // set focus
      scope.focus = function focus () {
        var searchBox = element.find('input')[0];
        searchBox.focus();
      };

      //required validator
      if (attrs.required || attrs.ngRequired) {
        required = true;
      }
      attrs.$observe('required', function (newVal) {
        required = newVal;
      });

      //watch disabled state
      scope.$watch(function () {
        return $parse(attrs.disabled)(scope);
      }, function (newVal) {
        scope.disabled = newVal;
      });

      //watch single/multiple state for dynamically change single to multiple
      scope.$watch(function () {
        return $parse(attrs.multiple)(scope);
      }, function (newVal) {
        isMultiple = newVal || false;
      });

      //watch option changes for options that are populated dynamically
      scope.$watch(function () {
        return parsedResult.source(scope);
      }, function (newVal) {
        if (angular.isDefined(newVal)) {
          parseModel();
        }
      }, true);

      //watch model change
      scope.$watch(function () {
        return ngModelCtrl.$modelValue;
      }, function (newVal) {
        //when directive initialize, newVal usually undefined. Also, if model value already set in the controller
        //for preselected list then we need to mark checked in our scope item. But we don't want to do this every time
        //model changes. We need to do this only if it is done outside directive scope, from controller, for example.

        if (angular.isDefined(newVal)) {
          markChecked(newVal);
          scope.$eval(changeHandler);
        }
        getHeaderText();
        ngModelCtrl.$setValidity('required', scope.valid());
      }, true);

      function parseModel
      () {
        scope.items.length = 0;
        var model = parsedResult.source(scope);
        if (!angular.isDefined(model)) {
          return;
        }
        for (var i = 0; i < model.length; i++) {
          var local = {};
          local[parsedResult.itemName] = model[i];
          scope.items.push({
            label: parsedResult.viewMapper(local),
            model: model[i],
            checked: false
          });
        }
      }

      parseModel();

      element.append($compile(popUpEl)(scope));

      function getHeaderText () {
        if (is_empty(ngModelCtrl.$modelValue)) {
          scope.header = 'Select';
          return scope.header;
        }
        if (isMultiple) {
          scope.header = ngModelCtrl.$modelValue.length + ' ' + 'selected';
        } else {
          var local = {};
          local[parsedResult.itemName] = ngModelCtrl.$modelValue;

          scope.header = parsedResult.viewMapper(local);
        }
      }

      function is_empty (obj) {
        if (!obj) {
          return true;
        }
        if (obj.length && obj.length > 0) {
          return false;
        }
        for (var prop in obj) {
          if (obj[prop]) {
            return false;
          }
        }
        return true;
      }

      scope.valid = function validModel () {
        if (!required) {
          return true;
        }
        var value = ngModelCtrl.$modelValue;
        return (angular.isArray(value) && value.length > 0) || (!angular.isArray(value) && value !== null);
      };

      function selectSingle (item) {
        if (item.checked) {
          scope.uncheckAll();
        } else {
          scope.uncheckAll();
          item.checked = !item.checked;
        }
        setModelValue(false);
      }

      function selectMultiple (item) {
        item.checked = !item.checked;
        setModelValue(true);
      }

      function setModelValue (isMultiple) {
        var value;

        if (isMultiple) {
          value = [];
          angular.forEach(scope.items, function (item) {
            if (item.checked) {
              value.push(item.model);
            }
          });
        } else {
          angular.forEach(scope.items, function (item) {
            if (item.checked) {
              value = item.model;
              return false;
            }
          });
        }
        ngModelCtrl.$setViewValue(value);
      }

      function markChecked (newVal) {
        if (!angular.isArray(newVal)) {
          angular.forEach(scope.items, function (item) {
            if (angular.equals(item.model, newVal)) {
              item.checked = true;
              return false;
            }
          });
        } else {
          angular.forEach(newVal, function (i) {
            angular.forEach(scope.items, function (item) {
              if (angular.equals(item.model, i)) {
                item.checked = true;
              }
            });
          });
        }
      }

      scope.checkAll = function () {
        if (!isMultiple) {
          return;
        }
        angular.forEach(scope.items, function (item) {
          item.checked = true;
        });
        setModelValue(true);
      };

      scope.uncheckAll = function () {
        angular.forEach(scope.items, function (item) {
          item.checked = false;
        });
        setModelValue(true);
      };

      scope.select = function (item) {
        if (isMultiple === false) {
          selectSingle(item);
          scope.toggleSelect();
        } else {
          selectMultiple(item);
        }
      };
    }
  };
};

