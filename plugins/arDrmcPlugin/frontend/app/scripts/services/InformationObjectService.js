'use strict';

module.exports = function ($http, $q, ATOM_CONFIG) {

  this.getTree = function (id) {
    return $http({
      method: 'GET',
      url: ATOM_CONFIG.frontendPath + 'api/informationobjects/' + id + '/tree'
    });
  };

  this.getById = function (id, params) {
    params = params || {};
    return $http({
      method: 'GET',
      url: ATOM_CONFIG.frontendPath + 'api/informationobjects/' + id,
      params: params
    });
  };

  this.get = function (params) {
    return $http({
      method: 'GET',
      url: ATOM_CONFIG.frontendPath + 'api/informationobjects',
      params: params
    });
  };

  this.getWorks = function (params) {
    params.level = 109;
    return this.get(params);
  };

  this.getWork = function (id) {
    return this.getById(id, {
      level_id: 109
    });
  };

};
