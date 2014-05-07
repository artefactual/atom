'use strict';

module.exports = function ($http, SETTINGS) {

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

  this.reclassifyAIP = function (uuid, typeId) {
    return $http({
      method: 'POST',
      url: SETTINGS.frontendPath + 'api/aips/' + uuid + '/reclassify',
      data: {
        type_id: parseInt(typeId, 10)
      }
    });
  };
};
