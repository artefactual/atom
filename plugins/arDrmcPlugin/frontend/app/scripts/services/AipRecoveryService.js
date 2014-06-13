'use strict';

module.exports = function ($http, SETTINGS) {
  this.getUuidsOfAipsMatchingStatus = function (status) {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/aips/status',
      params: {
        status: status
      }
    });
  };

  this.recoverAip = function (uuid) {
    return $http({
      method: 'POST',
      url: SETTINGS.frontendPath + 'api/aips/' + uuid + '/recover'
    });
  };
};
