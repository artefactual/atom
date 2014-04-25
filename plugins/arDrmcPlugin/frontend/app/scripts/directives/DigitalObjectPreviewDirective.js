'use strict';

module.exports = function ($compile, $http, ModalDigitalObjectViewerService) {
  return {
    restrict: 'E',
    replace: true,
    scope: {
      file: '='
    },
    link: function (scope, element) {
      // template: '<div><iframe width="420" height="315" src="//www.youtube.com/embed/Q-XD6fuf0ho" frameborder="0" allowfullscreen></iframe></div>',
      var mediaTypeId = scope.file.media_type_id;
      var templateUrl = ModalDigitalObjectViewerService.mediaTypes[mediaTypeId].templateUrl;

      // Fetch the template, bind it to a new scope and compile
      // TODO: doesn't AngularJS have a mixing for this?
      // TODO: should make use of preLink / postLink (compile vs link)?
      $http.get(templateUrl).then(function (response) {
        var templateScope = scope.$new();
        element.html(response.data);
        $compile(element.contents())(templateScope);
      });
    }
  };
};
