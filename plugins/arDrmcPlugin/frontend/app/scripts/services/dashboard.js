'use strict';

angular.module('momaApp.services')
  .factory('dashboardService', function () {

    var runUserRequest = function() {
      return 'foobar';
    };

    return {
      getOverview: function() {
        return runUserRequest();
      }
    };

  });
