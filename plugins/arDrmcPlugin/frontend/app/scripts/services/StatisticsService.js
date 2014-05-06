'use strict';

module.exports = function ($http, SETTINGS) {

  /**
   * API endpoints
   *
   * - /api/activity/downloads
   * - /api/activity/ingestion
   * - /api/summary/ingestion
   * - /api/summary/artworkbymonth
   * - /api/summary/mediafilesizebycollectionyear
   * - /api/summary/mediacategorycount
   * - /api/summary/storagebymediacategory
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

  this.getArtworkSizesByYearSummary = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/summary/mediafilesizebycollectionyear'
    });
  };
  /*
  this.getMonthlyTotalByCodec = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'end point needed'
    });
  };

  this.getMonthlyTotalByFormats = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'end point needed'
    });
  };

  this.getRunningTotalByCodec = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'end point needed'
    });
  };
  */

  this.getRunningTotalByFormats = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/summary/storagebymediacategory'
    });
  };

};
