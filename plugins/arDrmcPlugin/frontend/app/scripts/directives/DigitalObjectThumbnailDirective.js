(function () {

  'use strict';

  module.exports = function ($compile) {

    var templates = {
      'icon':      '<a href ng-click="click()">' +
                   '  <div class="thumb thumb-icon" style="width: {{ width }}; height: {{ height }};">' +
                   '    <span ng-class="[\'icon\', getIcon()]"></span>' +
                   '  </div>' +
                   '</a>',

      'thumbnail': '<a href ng-click="click()">' +
                   '  <div class="thumb thumb-preview" style="width: {{ width }}; height: {{ height }}; background-image: url({{ thumbnailPath }})">' +
                   '  </div>' +
                   '</a>'
    };

    // PUID => MIME types
    // http://www.nationalarchives.gov.uk/documents/DROID_SignatureFile_V74.xml

    // Extract the top-level media type from a MIME type
    function getTypeParts (mediaType) {
      if (!angular.isString(mediaType) || !mediaType.length) {
        return null;
      }
      var parts = mediaType.split('/');
      if (parts.length !== 2 || parts[0] === '' || parts[1] === '') {
        return null;
      }
      return {
        full: mediaType,
        prefix: parts[0],
        suffix: parts[1]
      };
    }

    // Relate each major Internet media type with an icon
    // Omitted: example, message and model
    var icons = {
      'application': {
        '_': 'icon-desktop',
        'xml': 'icon-code'
      },
      'audio': {
        '_': 'icon-volume-up'
      },
      'image': {
        '_': 'icon-picture'
      },
      'multipart': {
        '_': 'icon-envelope-alt'
      },
      'text': {
        '_': 'icon-file-text-alt'
      },
      'video': {
        '_': 'icon-film'
      },
      'unknown': 'icon-question-sign'
    };

    return {
      restrict: 'E',
      replace: true,
      scope: {
        thumbnailPath: '@',
        mediaType: '@',
        width: '@',
        height: '@',
        onClick: '&'
      },
      compile: function () {
        return function postLink (scope, element) {

          // Load the right template and compile it
          var template = templates.thumbnail;
          if (!scope.thumbnailPath.length) {
            template = templates.icon;
          }
          element.html(template).show();
          $compile(element.contents())(scope);

          // Get top-level media type and its corresponding icon
          var typeParts = getTypeParts(scope.mediaType);
          scope.getIcon = function () {
            if (typeParts === null) {
              return icons.unknown;
            }
            var set = icons[typeParts.prefix];
            if (angular.isUndefined(set)) {
              return icons.unknown;
            }
            if (angular.isDefined(set[typeParts.suffix])) {
              return set[typeParts.suffix];
            }
            return set._;
          };

          scope.click = function () {
            scope.onClick.call();
          };

        };
      }
    };

  };

})();
