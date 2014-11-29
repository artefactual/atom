(function () {

  'use strict';

  angular.module('drmc.directives').directive('arContextBrowserMetadata', function (SETTINGS) {

    var dcFields = [
      'identifier',
      'title',
      'description',
      'names',
      'dates',
      'types',
      'format',
      'source',
      'rights'
    ];

    return {
      restrict: 'E',
      templateUrl: SETTINGS.viewsPath + '/partials/context-browser.metadata.html',
      replace: true,
      link: function (scope, element, attrs) {

        scope.fields = dcFields;

        scope.$watch(attrs.metadata, function (value) {
          if (typeof value !== 'undefined') {
            scope.metadata = value;
          }
        });

        scope.isList = function (value) {
          return angular.isArray(value);
        };

        scope.hasField = function (name) {
          return scope.metadata.hasOwnProperty(name);
        };

        scope.renderField = function (name) {
          var value = scope.metadata[name];
          if (angular.isObject(value)) {
            return scope.renderObject(value, name);
          }
          return scope.renderValue(value);
        };

        scope.renderValue = function (value) {
          return value;
        };

        scope.renderObject = function (value, name) {
          switch (name) {
            case 'dates':
              var el = [];
              ['date', 'start_date', 'end_date'].forEach(function (e) {
                if (!value.hasOwnProperty(e)) {
                  return;
                }
                el.push(value[e]);
              });
              return el.join(', ');
            case 'names':
              return value.authorized_form_of_name;
            case 'types':
              return value.name;
          }
        };

        scope.collapsed = false;
        scope.toggle = function () {
          scope.collapsed = !scope.collapsed;
        };

      }
    };

  });

})();
