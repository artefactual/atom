'use strict';

module.exports = function ($http, SETTINGS) {

  // Temporary hack to deal with constant IDs in AtoM
  // This should be probably a feature provided by the API
  var taxonomies = {
    'EVENT_TYPE': 40,
    'DC_TYPES': 54,
    'SUPORTING_TECHNOLOGY_RELATION_TYPES': SETTINGS.drmc.taxonomy_supporting_technologies_relation_types_id
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
