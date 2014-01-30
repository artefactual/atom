'use strict';

module.exports = function (ATOM_CONFIG, InformationObjectService, FullscreenService) {
  return {
    restrict: 'E',
    templateUrl: ATOM_CONFIG.viewsPath + '/partials/context-browser.html',
    scope: {
      resource: '@resource'
    },
    replace: true,
    link: function (scope, element) {
      // This layer will be the closest HTML container of the SVG
      var container = element.find('.svg-container');

      // Do I really need this because I'm isolating scope with @?
      // attr.$observe('resource', function (value) {
      //  console.log(value);
      //});

      InformationObjectService.getTree(scope.resource)
        .then(function (tree) {
          new (require('../lib/cbd'))(container, tree);
        }, function (reason) {
          console.error('Error loading tree:', reason);
        });

      scope.isFullscreen = false;
      scope.toggleFullscreenMode = function () {
        if (scope.isFullscreen) {
          FullscreenService.cancel();
        } else {
          FullscreenService.enable(element.get(0));
        }
        scope.isFullscreen = !scope.isFullscreen;
      };
      scope.$on('fullscreenchange', function (event, args) {
        if (args.type === 'enter') {
          scope.isFullscreen = true;
        } else {
          scope.isFullscreen = false;
        }
      });
    }
  };
};
