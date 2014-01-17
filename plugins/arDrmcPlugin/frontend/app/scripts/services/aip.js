'use strict';

angular.module('momaApp.services')
  .service('AIPService', function ($http, atomGlobals) {

    this.getOverview = function() {
      return $http({
        method: 'GET',
        url: atomGlobals.relativeUrlFrontend + '/api/aips'
      });
    };

  });
