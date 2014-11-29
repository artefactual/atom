(function () {

  'use strict';

  angular.module('drmc.services').service('AIPService', function ($http, SETTINGS) {

    this.getAIP = function (uuid) {
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/aips/' + uuid
      });
    };

    this.getAIPs = function (params) {
      params = params || {};
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/aips',
        params: params
      });
    };

    this.getFiles = function (uuid, params) {
      params = params || {};
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/aips/' + uuid + '/files',
        params: params
      });
    };

    this.reclassifyAIP = function (uuid, typeId) {
      return $http({
        method: 'POST',
        url: SETTINGS.frontendPath + 'api/aips/' + uuid + '/reclassify',
        data: {
          type_id: parseInt(typeId, 10)
        }
      });
    };

    this.recoverAip = function (reportId) {
      return $http({
        method: 'POST',
        url: SETTINGS.frontendPath + 'api/recover/' + reportId
      });
    };

  });

})();
