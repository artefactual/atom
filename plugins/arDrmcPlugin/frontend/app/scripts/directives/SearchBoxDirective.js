(function () {

  'use strict';

  angular.module('drmc.directives').directive('arSearchBox', function ($rootScope, $window, $document, $location, $state, $stateParams, $sce, SETTINGS, SearchService) {

    return {
      restrict: 'E',
      templateUrl: SETTINGS.viewsPath + '/partials/search-box.html',
      replace: true,
      link: function (scope, element) {
        // Visibility options
        scope.showRealm = false;
        scope.allowInputBlur = true;

        // Default realm
        scope.realm = 'all';

        // Reference to the input
        var input = element.find('input[type=text]');

        // Autocomplete listener
        scope.$watch('query', function () {
          search(scope.query);
        });

        // Hack input blur. Don't loose input focus if realm is open
        input.on('blur', function () {
          if (!scope.allowInputBlur) {
            input.focus();
          } else {
            scope.showRealm = false;
          }
        });

        // Listen to keyup events like ESC, etc...
        input.on('keyup', function (event) {
          switch (event.which) {
            // Escape
            case 27:
              scope.$apply(function () {
                scope.clear();
              });
              break;
            // Enter
            case 13:
              // Do nothing!
              break;
            default:
              if (!scope.showRealm) {
                scope.$apply(function () {
                  scope.showRealm = true;
                });
              }
              break;
          }
        });

        // Toggle realm depending on when the user clicks
        // Tried with click, but doesn't work with the browse dropdown
        $document.on('mouseup', function (event) {
          var isTouching = element.has(event.target).length > 0;
          if (isTouching && !scope.showRealm) {
            scope.$apply(function () {
              scope.showRealm = true;
              scope.allowInputBlur = false;
            });
          } else if (!isTouching && scope.showRealm) {
            scope.$apply(function () {
              scope.allowInputBlur = true;
              input.blur();
            });
          }
        });

        // Listen for browser loosing focus and hide realm
        angular.element($window).on('blur', function () {
          scope.$apply(function () {
            scope.allowInputBlur = true;
            scope.showRealm = false;
          });
        });

        scope.submit = function () {
          if (!scope.form.$valid) {
            return;
          }
          // Route user to the corresponding search page
          SearchService.setQuery(scope.query, scope.realm);
          var entity = scope.realm === 'all' ? 'works' : scope.realm;
          $state.go('main.search.entity', { entity: entity }).then(function () {
            scope.clear();
          });
        };

        scope.clear = function () {
          scope.showRealm = false;
          scope.allowInputBlur = true;
          input.blur();
          scope.query = undefined;
          delete scope.results;
        };

        scope.showAll = function (entity) {
          scope.realm = entity;
          scope.submit();
        };

        var search = function (query) {
          if (!angular.isDefined(query) || query.length < 1) {
            delete scope.results;
            return;
          }
          var options = { realm: scope.realm };
          SearchService.autocomplete(query, options).then(function (results) {
            // Turn properties with <em> highlight to trusted html
            // TODO: Is there a workaround? Five loops here seems to me like a
            // terrible idea!
            for (var key in results.data.aips) {
              var aip = results.data.aips[key];
              if (aip.hasOwnProperty('name')) {
                aip.name = $sce.trustAsHtml(aip.name);
              }
            }
            for (key in results.data.artworks) {
              var artwork = results.data.artworks[key];
              if (artwork.hasOwnProperty('title')) {
                artwork.title = $sce.trustAsHtml(artwork.title);
              }
            }
            for (key in results.data.components) {
              var comp = results.data.components[key];
              if (comp.hasOwnProperty('title')) {
                comp.title = $sce.trustAsHtml(comp.title);
              }
            }
            for (key in results.data.technology_records) {
              var techRecord = results.data.technology_records[key];
              if (techRecord.hasOwnProperty('title')) {
                techRecord.title = $sce.trustAsHtml(techRecord.title);
              }
            }
            for (key in results.data.files) {
              var file = results.data.files[key];
              if (file.hasOwnProperty('title')) {
                file.title = $sce.trustAsHtml(file.title);
              }
            }
            scope.results = results.data;
          }, function () {
            delete scope.results;
          });
        };

        // Clean search field if the user changes the page
        $rootScope.$on('$stateChangeSuccess', function () {
          if (!$state.includes('search')) {
            scope.query = '';
          }
        });
      }
    };

  });

})();
