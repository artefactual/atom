'use strict';

module.exports = function ($http, SETTINGS) {

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
      url: SETTINGS.frontendPath + 'api/reports/' + slug,
    });
  };

  this.deleteReport = function (id) {
    return $http({
      method: 'DELETE',
      url: SETTINGS.frontendPath + 'api/reports/' + id
    });
  };

};
