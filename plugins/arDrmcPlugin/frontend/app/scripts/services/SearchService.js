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

  this.searches = [
    {
      name: 'AIPs',
      entity: 'aips'
    },
    {
      name: 'Artwork records',
      entity: 'works'
    },
    {
      name: 'Components',
      entity: 'components'
    },
    {
      name: 'Supporting technology records',
      entity: 'technology-records'
    },
    {
      name: 'Files',
      entity: 'files'
    }
  ];

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

  this.search = function (entity, params) {
    // WIP This is going to need some work
    switch (entity) {
      case 'aips':
        return AIPService.getAIPs(params);
      case 'works':
        return InformationObjectService.getWorks(params);
      case 'components':
        return InformationObjectService.getComponents(params);
    }
  };
};
