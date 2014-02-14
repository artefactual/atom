'use strict';

module.exports = function ($http, ATOM_CONFIG) {

  this.getAIPs = function (params) {
    params = (typeof params === 'undefined') ? {} : params;

    return $http({
      method: 'GET',
      url: ATOM_CONFIG.frontendPath + 'api/aips',
      params: params
    });
  };

  this.reclassifyAIP = function (id, classification) {
    return $http({
      method: 'POST',
      url: ATOM_CONFIG.frontendPath + 'api/aips',
      data: {
        id: id,
        classification: classification
      }
    });
  };
};
