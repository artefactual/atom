'use strict';

module.exports = function ($http) {
	
  this.getAIPs = function () {
    return $http({
      method: 'GET',
      url: '/api/aips'
    });
  };

};
