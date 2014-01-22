'use strict';

angular.module('momaApp.services')
  .service('dashboardService', function ($http, ATOM_CONFIG) {

    this.getOverview = function () {
      return $http({
        method: 'GET',
        url: ATOM_CONFIG.frontendPath + '/api/dashboard'
      });
    };

  });
