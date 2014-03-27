'use strict';

module.exports = function ($http, SETTINGS, AIPService, InformationObjectService) {
  // Shared query between controllers, originated in the header search box
  this.query = null;
  this.setQuery = function (q) {
    if (this.query === q) {
      return;
    }
    this.query = q;
  };

  this.autocomplete = function (query, params) {
    params = params || {};
    params.query = query;
    var configuration = {
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/search/autocomplete',
      params: params
    };
    if (Object.keys(params).length > 0) {
      configuration.params = params;
    }
    return $http(configuration);
  };

  this.search = function (query, entity) {
    // WIP This is going to need some work
    switch (entity) {
      case 'aips':
        return AIPService.getAIPs({
          query: query
        });
      case 'artworks':
        return InformationObjectService.getWorks(query);
      case 'components':
        return InformationObjectService.getComponents(query);
    }
  };
};
