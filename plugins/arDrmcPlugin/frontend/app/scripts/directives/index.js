(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.directives', [])

    .directive('arContextBrowserArtwork', require('./ContextBrowserArtworkDirective'))
    .directive('arContextBrowserTechnology', require('./ContextBrowserTechnologyDirective'))
    .directive('arSortHeader', require('./SortHeaderDirective'))
    .directive('arDigitalObjectPreview', require('./DigitalObjectPreviewDirective'))
    .directive('arDigitalObjectViewerSidebar', require('./DigitalObjectViewerSidebarDirective'))
    .directive('arDigitalObjectThumbnail', require('./DigitalObjectThumbnailDirective'))
    .directive('arFacet', require('./FacetDirective'))
    .directive('arPager', require('./PagerDirective'))
    .directive('arToggleSlide', require('./ToggleSlideDirective'))
    .directive('arSearchBox', require('./SearchBoxDirective'))
    .directive('arRangeFacet', require('./RangeFacetDirective'))
    .directive('arDateRangeField', require('./DateRangeFieldDirective'))
    .directive('arGraphLine', require('./GraphLineDirective'))
    .directive('arGraphPie', require('./GraphPieDirective'));

})();
