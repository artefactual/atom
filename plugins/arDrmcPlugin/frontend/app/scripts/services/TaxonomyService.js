'use strict';

module.exports = function ($http, SETTINGS) {

  // Temporary hack to deal with constant IDs in AtoM
  // This should be probably a feature provided by the API
  var taxonomies = {
    'EVENT_TYPE': 40,
    'DC_TYPES': 54
  };

  this.getTerms = function (taxonomy) {
    var configuration = {
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/taxonomies/' + taxonomies[taxonomy]
    };
    return $http(configuration).then(function (response) {
      return response.data;
    });
  };

};
