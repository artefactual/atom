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
      entity: 'aips',
      cssClass: 'drmc-color-aip'
    },
    {
      name: 'Artwork records',
      entity: 'works',
      cssClass: 'drmc-color-artwork-record'
    },
    {
      name: 'Components',
      entity: 'components',
      cssClass: 'drmc-color-component'
    },
    {
      name: 'Supporting technology records',
      entity: 'technology-records',
      cssClass: 'drmc-color-supporting-technology-record'
    },
    {
      name: 'Files',
      entity: 'files',
      cssClass: 'drmc-color-file'
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
      case 'technology-records':
        return InformationObjectService.getSupportingTechnologyRecords(params);
      case 'files':
        return InformationObjectService.getFiles(params);
    }
  };

  this.getSearchById = function (id) {
    var configuration = {
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/searches/' + id,
    };
    return $http(configuration).then(function (response) {
      return response.data;
    });
  };

  this.createSearch = function (data) {
    var configuration = {
      method: 'POST',
      url: SETTINGS.frontendPath + 'api/searches',
      data: data
    };
    return $http(configuration).then(function (response) {
      return response.data;
    });
  };

  this.updateSearch = function (id, data) {
    var configuration = {
      method: 'PUT',
      url: SETTINGS.frontendPath + 'api/searches/' + id,
      data: data
    };
    return $http(configuration).then(function (response) {
      return response.data;
    });
  };

  this.deleteSearch = function (id) {
    return $http({
      method: 'DELETE',
      url: SETTINGS.frontendPath + 'api/searches/' + id
    });
  };
};
