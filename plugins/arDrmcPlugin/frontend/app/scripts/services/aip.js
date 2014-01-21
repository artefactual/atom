'use strict';

angular.module('momaApp.services')
  .service('AIPService', function ($http, ATOM_CONFIG) {

    this.getAIPs = function() {
      return $http({
        method: 'GET',
        url: ATOM_CONFIG.frontendPath + '/api/aips'
      });
    };

  });
