'use strict';

module.exports = function ($compile, $http, $timeout, ModalDigitalObjectViewerService) {
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
        var mediaTypeId = scope.file.media_type_id;
        var templateUrl = ModalDigitalObjectViewerService.mediaTypes[mediaTypeId].templateUrl;

        // Fetch the template, bind it to a new scope and compile
        $http.get(templateUrl).then(function (response) {
          var templateScope = scope.$new();
          element.html(response.data);
          $compile(element.contents())(templateScope);
        });
      };

      // Whenever scope.file changes we have to render again
      scope.$watch('file', function () {
        render();
      });

      var dovModalBody = element.parent();
      $timeout(function () {
        var w = dovModalBody.width();
        var h = dovModalBody.height();
        dovModalBody.find('dov-modal-body-content .text object').remove().css({
          'width': w,
          'height': h
        });
      }, 0);

    }
  };
};
