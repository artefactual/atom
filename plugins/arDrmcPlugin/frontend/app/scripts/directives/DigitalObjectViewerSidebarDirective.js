'use strict';

module.exports = function (SETTINGS, InformationObjectService, ModalDigitalObjectViewerService) {
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
        scope.mediaType = ModalDigitalObjectViewerService.mediaTypes[value.media_type_id].class;
      });

      scope.close = function () {
        scope._close();
      };

    }
  };
};
