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
   //var downloadActivity
  this.getDownloadActivity = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/activity/downloads'
    });
  };
  // var ingestionActivity
  this.getIngestionActivity = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/activity/ingestion'
    });
  };
  // var ingestionSummary
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

  // This does not return codecs, only totals
  this.getMonthlyTotalByCodec = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/summary/artworkbymonth'
    });
  };

  // var storageCodec
  this.getRunningTotalByCodec = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/summary/mediacategorycount'
    });

  };
  // var storageFormats
  this.getRunningTotalByFormats = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/summary/storagebymediacategory'
    });
  };

};
