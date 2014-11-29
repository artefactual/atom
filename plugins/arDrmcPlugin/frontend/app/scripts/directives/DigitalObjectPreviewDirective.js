(function () {

  'use strict';

  angular.module('drmc.directives').directive('arDigitalObjectPreview', function ($compile, $http, SETTINGS, ModalDigitalObjectViewerService) {

    return {
      restrict: 'A',
      replace: true,
      scope: {
        file: '='
      },
      link: function (scope, element) {

        // Fetch the template and compile it, linked to this scope
        // TODO: should make use of preLink / postLink (compile vs link)?
        var render = function () {
          var templateUrl = SETTINGS.viewsPath + '/partials/digital-object-preview.other.html';
          if (angular.isDefined(scope.file.media_type_id)) {
            var mt = ModalDigitalObjectViewerService.mediaTypes[scope.file.media_type_id];
            if (angular.isDefined(mt.templateUrl)) {
              templateUrl = mt.templateUrl;
            }
          }

          // Fetch the template, bind it to a new scope and compile
          $http({
            method: 'GET',
            url: templateUrl,
            cache: true
          }).then(function (response) {
            element.html(response.data);
            $compile(element.contents())(scope.$new());
          });
        };

        // Whenever scope.file changes we have to render again
        scope.$watch('file', function () {
          render();
        });

      }
    };

  });

})();
