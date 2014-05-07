'use strict';

module.exports = function (SETTINGS) {
  return {
    restrict: 'E',
    templateUrl: SETTINGS.viewsPath + '/partials/range-facet.html',
    replace: true,
    scope: {
      type: '@',
      label: '@',
      facet: '=',
      from: '=',
      to: '=',
      callback: '&'
    },
    link: function (scope) {
      scope.collapsed = false;
      scope.collapsedRangePicker = true;
      scope.dateRangePickerFrom = undefined;
      scope.dateRangePickerTo = undefined;
      scope.sizeRangePickerFrom = undefined;
      scope.sizeRangePickerTo = undefined;
      scope.units = [
        { label: 'bytes', value: 1 },
        { label: 'KB', value: 1024 },
        { label: 'MB', value: 1048576 },
        { label: 'GB', value: 1073741824 },
        { label: 'TB', value: 1099511627776 },
        { label: 'PB', value: 1125899906842624 }
      ];
      scope.sizeRangePickerFromUnit = scope.units[0];
      scope.sizeRangePickerToUnit = scope.units[0];

      scope.toggle = function () {
        scope.collapsed = !scope.collapsed;
      };

      scope.toggleRangePicker = function () {
        scope.collapsedRangePicker = !scope.collapsedRangePicker;
      };

      scope.select = function (from, to) {
        if (scope.from === from && scope.to === to) {
          scope.from = undefined;
          scope.to = undefined;
          return;
        }
        scope.from = from;
        scope.to = to;
      };

      scope.isSelected = function (from, to) {
        return scope.from === from && scope.to === to;
      };

      scope.getLabel = function (from, to) {
        if (scope.label === 'Date ingested' || scope.label === 'Date materials ingested') {
          return scope.callback({arg1: 'dateIngested', arg2: from, arg3: to});
        }
        if (scope.label === 'Date of acquisition') {
          return scope.callback({arg1: 'dateCollected', arg2: from, arg3: to});
        }
        if (scope.label === 'Date created') {
          return scope.callback({arg1: 'dateCreated', arg2: from, arg3: to});
        }
        if (scope.label === 'Date updated') {
          return scope.callback({arg1: 'dateUpdated', arg2: from, arg3: to});
        }
        return scope.callback({arg1: from, arg2: to});
      };

      scope.resetRangePicker = function () {
        scope.sizeRangePickerFrom = scope.dateRangePickerFrom = scope.from = undefined;
        scope.sizeRangePickerTo = scope.dateRangePickerTo = scope.to = undefined;
        scope.sizeRangePickerFromUnit = scope.units[0];
        scope.sizeRangePickerToUnit = scope.units[0];
      };

      scope.submitRangePicker = function () {
        if (scope.type === 'date') {
          if (scope.dateRangePickerFrom !== undefined) {
            scope.from = new Date(scope.dateRangePickerFrom).getTime();
          } else {
            scope.from = scope.dateRangePickerFrom = undefined;
          }
          if (scope.dateRangePickerTo !== undefined) {
            scope.to = new Date(scope.dateRangePickerTo).getTime();
          } else {
            scope.to = scope.dateRangePickerTo = undefined;
          }
        }
        if (scope.type === 'size') {
          if (scope.sizeRangePickerFrom !== undefined && !isNaN(scope.sizeRangePickerFrom)) {
            scope.from = parseInt(scope.sizeRangePickerFrom) * scope.sizeRangePickerFromUnit.value;
          } else {
            scope.from = scope.sizeRangePickerFrom = undefined;
          }
          if (scope.sizeRangePickerTo !== undefined && !isNaN(scope.sizeRangePickerTo)) {
            scope.to = parseInt(scope.sizeRangePickerTo) * scope.sizeRangePickerToUnit.value;
          } else {
            scope.to = scope.sizeRangePickerTo = undefined;
          }
        }
        if (scope.type === 'dateYear') {
          if (scope.dateRangePickerFrom !== undefined && scope.dateRangePickerFrom.match(/\d{4}/)) {
            scope.from = new Date(scope.dateRangePickerFrom).getTime();
          } else {
            scope.from = scope.dateRangePickerFrom = undefined;
          }
          if (scope.dateRangePickerTo !== undefined && scope.dateRangePickerTo.match(/\d{4}/)) {
            scope.to = new Date(scope.dateRangePickerTo).getTime();
          } else {
            scope.to = scope.dateRangePickerTo = undefined;
          }
        }
      };
    }
  };
};
