'use strict';

module.exports = function ($http, SETTINGS) {

  /**
   * API endpoints
   *
   * - /api/activity/downloads
   * - /api/activity/ingestion
   * - /api/summary/ingestion
   * - /api/summary/artworkbydate
   * - /api/summary/mediafilesizebycollectionyear
   * - /api/summary/departmentartworkcount
   * - /api/summary/storagebycodec
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
      url: SETTINGS.frontendPath + 'api/summary/artworkbydate'
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
      url: SETTINGS.frontendPath + 'api/summary/artworkbydate'
    });
  };

  // var storageCodec
  this.getRunningTotalByDepartment = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/summary/departmentartworkcount'
    });

  };
  // var storageFormats
  this.getRunningTotalByCodec = function () {
    return $http({
      method: 'GET',
      url: SETTINGS.frontendPath + 'api/summary/storagebycodec'
    });
  };

};
