(function () {

  'use strict';

  module.exports = function ($compile) {

    var templates = {
      'icon':      '<div class="thumb thumb-icon">' +
                   '  <span ng-class="getIcon()">ICON: {{ getIcon() }} class (mediaType)</span>' +
                   '</div>',

      'thumbnail': '<div class="thumb thumb-preview">' +
                   '  <!-- This could make use of the background tile hack in HTML5 -->            ' +
                   '  <img ng-src="{{ thumbnailPath }}" width="{{ width }}" height="{{ height }}"/>' +
                   '</div>'
    };

    // PUID => MIME types
    // http://www.nationalarchives.gov.uk/documents/DROID_SignatureFile_V74.xml

    // Extract the top-level media type from a MIME type
    function getTopLevelType (mediaType) {
      if (!angular.isString(mediaType)) {
        return null;
      }
      var regex = /^[^\/]*$/;
      var matches = mediaType.match(regex);
      if (matches === null) {
        return null;
      }
      return matches[0];
    }

    // Relate each major Internet media type with an icon
    // Omitted: example, message and model
    var icons = {
      'application': 'icon-desktop',
      'audio': 'icon-volume-up',
      'image': 'icon-picture',
      'multipart': 'icon-envelope-alt',
      'text': 'icon-file-text-alt',
      'video': 'icon-film',
      'unknown': 'icon-?'
    };

    console.log(icons);

    return {
      restrict: 'E',
      replace: true,
      scope: {
        thumbnailPath: '@',
        mediaType: '@',
        width: '@',
        height: '@'
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
          var topLevelType = getTopLevelType(scope.mediaType);
          scope.getIcon = function () {
            if (!scope.mediaType.length || topLevelType === null) {
              return icons.unknown;
            }
            return icons[topLevelType];
          };

        };
      }
    };

  };

})();
