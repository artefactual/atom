'use strict';

module.exports = function (SETTINGS, $http) {

  this.getAIPFixity = function (uuid) {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/fixity/' + uuid
    });
  };

  this.getDashboardFixity = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/fixitywidget'
    });
  };

};
