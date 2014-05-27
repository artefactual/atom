'use strict';

module.exports = function (SETTINGS, $http) {

  this.getAipFixity = function (uuid) {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/fixity/' + uuid
    });
  };

  this.getDashboardFixity = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/fixity/status'
    });
  };

};
