'use strict';

module.exports = function ($http, SETTINGS) {

  /**
   * API endpoints
   *
   * - /api/activity/downloads
   * - /api/activity/ingestion
   * - /api/summary/ingestion
   * - /api/summary/artworkbymonth
   *
   */

  this.getDownloadActivity = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/activity/downloads'
    });
  };

  this.getIngestionActivity = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/activity/ingestion'
    });
  };

  this.getIngestionSummary = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/summary/ingestion'
    });
  };

  this.getArtworkByMonthSummary = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/summary/artworkbymonth'
    });
  };

};
