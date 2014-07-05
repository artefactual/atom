'use strict';

module.exports = function ($http, SETTINGS) {

  this.types = [
    {
      'name': 'High-level ingest report (activity)',
      'type': 'high_level_ingest'
    },
    {
      'name': 'Granular ingest report (activity)',
      'type': 'granular_ingest'
    },
    {
      'name': 'General download report (activity)',
      'type': 'general_download'
    },
    {
      'name': 'Amount downloaded report (activity)',
      'type': 'amount_downloaded'
    },
    {
      'name': 'Full fixity report (fixity)',
      'type': 'fixity'
    },
    {
      'name': 'Fixity error report (fixity)',
      'type': 'fixity_error'
    },
    {
      'name': 'Video characteristics report (characteristic)',
      'type': 'video_characteristics'
    },
    {
      'name': 'Component-level report (characteristic)',
      'type': 'component_level'
    }
  ];

  this.getTitleByType = function (type) {
    for (var i = 0; i < this.types.length; i++) {
      var t = this.types[i];
      if (t.type === type) {
        return t.name;
      }
    }
  };

  this.getBrowse = function (params) {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/reports/browse',
      params: params
    });
  };

  this.getReportResults = function (params) {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/report',
      params: params
    });
  };

  this.saveReport = function (data) {
    data = data || {};
    if (angular.isDefined(data.from) || angular.isDefined(data.to)) {
      data.range = {};
      if (angular.isDefined(data.from)) {
        data.range.from = new Date(data.from).getTime();
        delete data.from;
      }
      if (angular.isDefined(data.to)) {
        data.range.to = new Date(data.to).getTime();
        delete data.to;
      }
    }
    return $http({
      method: 'POST',
      url: SETTINGS.frontendPath + 'api/report',
      data: data
    });
  };

  this.getReportBySlug = function (slug) {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/reports/' + slug
    });
  };

  this.deleteReport = function (id) {
    return $http({
      method: 'DELETE',
      url: SETTINGS.frontendPath + 'api/reports/' + id
    });
  };

};
