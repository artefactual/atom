'use strict';

module.exports = function (SETTINGS, $http) {

  this.getAipFixity = function (uuid) {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/fixity/' + uuid
    });
  };

  this.getStatusFixity = function (params) {
    params = params || {};
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/fixity/status',
      params: params
    });
  };

};
