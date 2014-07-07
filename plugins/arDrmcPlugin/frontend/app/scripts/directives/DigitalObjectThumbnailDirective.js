(function () {

  'use strict';

  module.exports = function ($compile) {

    var templates = {
      'icon':      '<div class="thumb">' +
                   '  <span ng-if="mediaType">ICON: {{ mediaType }} (mediaType)</span>' +
                   '  <span ng-if="!mediaType">ICON: Unknown mediaType</span>' +
                   '</div>',

      'thumbnail': '<div class="thumb">' +
                   '  <img ng-src="{{ thumbnailPath }}" width="{{ width }}" height="{{ height }}"/>' +
                   '</div>',
    };

    // PUID => MIME types
    // http://www.nationalarchives.gov.uk/documents/DROID_SignatureFile_V74.xml

    // Relate each major Internet media type with an icon
    // Omitted: example, message and model
    var icons = {
      'application': 'icon-desktop',
      'audio': 'icon-volume-up',
      'image': 'icon-picture',
      'multipart': 'icon-envelope-alt',
      'text': 'icon-file-text-alt',
      'video': 'icon-film'
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
          var template = templates.thumbnail;
          if (!scope.thumbnailPath.length) {
            template = templates.icon;
          }
          element.html(template).show();
          $compile(element.contents())(scope);
        };
      }
    };

  };

})();
