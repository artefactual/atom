'use strict';

module.exports = function ($http, ATOM_CONFIG) {

  this.getAIP = function (uuid) {
    return $http({
      method: 'GET',
      url: ATOM_CONFIG.frontendPath + 'api/aips/' + uuid
    });
  };

  this.getAIPs = function (params) {
    params = params || {};
    // PHP needs to suffix with [] if sending multiple params with the same key?
    // If this is going to stay here forever, let's have a mixin somewhere
    if (jQuery.isArray(params.type) && params.type.length > 0) {
      // TODO: type[] in Angular doesn't work?
      // params['type[]'] = params.type;
      // delete params.type;
    }
    return $http({
      method: 'GET',
      url: ATOM_CONFIG.frontendPath + 'api/aips',
      params: params
    });
  };

  this.reclassifyAIP = function (uuid, typeId) {
    return $http({
      method: 'POST',
      url: ATOM_CONFIG.frontendPath + 'api/aips/' + uuid + '/reclassify',
      data: {
        type_id: typeId
      }
    });
  };

  this.getTypes = function () {
    var taxonomyId = 71;
    return $http({
      cache: true,
      method: 'GET',
      url: ATOM_CONFIG.frontendPath + 'api/taxonomies/' + taxonomyId
    });
  };
};
