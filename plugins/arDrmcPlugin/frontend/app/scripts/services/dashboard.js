'use strict';

angular.module('momaApp.services')
  .factory('dashboardService', function ($http, ATOM_CONFIG) {

    var runUserRequest = function() {
      return 'foobar';
    };

    return {
      getOverview: function() {
        return runUserRequest();
      }
    };

  });
