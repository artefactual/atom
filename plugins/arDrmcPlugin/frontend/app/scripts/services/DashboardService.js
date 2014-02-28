'use strict';

module.exports = function ($http, SETTINGS) {

  this.getOverview = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/dashboard'
    });
  };

};
