'use strict';

module.exports = function ($http, ATOM_CONFIG) {

  this.getAIPs = function () {
    return $http({
      method: 'GET',
      url: ATOM_CONFIG.frontendPath + 'api/aips'
    });
  };

  this.reclassifyAIP = function (id, classification) {
    return $http({
      method: 'GET',
      url: ATOM_CONFIG.frontendPath + 'api/aips/reclassify',
      data: {
        id: id,
        classification: classification
      }
    });
  };

};
