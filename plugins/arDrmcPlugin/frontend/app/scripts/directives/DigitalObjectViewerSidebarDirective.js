(function () {

  'use strict';

  angular.module('drmc.directives').directive('arDigitalObjectViewerSidebar', function (SETTINGS, InformationObjectService, ModalDigitalObjectViewerService) {

    return {
      restrict: 'E',
      templateUrl: SETTINGS.viewsPath + '/partials/digital-object-viewer-sidebar.html',
      scope: {
        file: '=',
        _close: '&onClose'
      },
      replace: true,
      link: function (scope) {

        scope.showAipsArea = true;
        scope.showCharacterizationArea = true;
        scope.showMediaArea = false;

        scope.$watch('file', function (value) {
          InformationObjectService.getMets(value.id).then(function (response) {
            scope.mets = response.data;
          });
          if (angular.isDefined(value.media_type_id)) {
            var mt = ModalDigitalObjectViewerService.mediaTypes[value.media_type_id];
            if (angular.isDefined(mt) && angular.isDefined(mt.class)) {
              scope.mediaType = ModalDigitalObjectViewerService.mediaTypes[value.media_type_id].class;
            }
          }
        });

        scope.close = function () {
          scope._close();
        };

      }
    };

  });

})();
