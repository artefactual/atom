'use strict';

angular.module('momaApp.services')
  .factory('dashboardService', function ($http, atomGlobals) {

    var runUserRequest = function() {
      return 'foobar';
    };

    return {
      getOverview: function() {
        return runUserRequest();
      }
    };

  });
