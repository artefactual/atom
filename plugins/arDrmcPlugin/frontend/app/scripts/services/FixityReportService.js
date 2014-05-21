'use strict';

module.exports = function (SETTINGS, $http) {

  this.getFixityStatus = function (uuid) {

    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/fixityreports/' + uuid
    });
  };



};
