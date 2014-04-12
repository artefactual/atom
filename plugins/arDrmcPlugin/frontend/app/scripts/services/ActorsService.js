'use strict';

module.exports = function ($http, SETTINGS) {

  this.getActors = function (query) {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/actors',
      params: {
        query: query
      }
    }).then(function (response) {
      return response.data;
    });
  };

};
