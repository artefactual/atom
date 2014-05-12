(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.directives', [])
    .directive('arContextBrowserArtwork', require('./ContextBrowserArtworkDirective'))
    .directive('arContextBrowserTechnology', require('./ContextBrowserTechnologyDirective'))
    .directive('arSortHeader', require('./SortHeaderDirective'))
    .directive('arDigitalObjectPreview', require('./DigitalObjectPreviewDirective'))
    .directive('arFacet', require('./FacetDirective'))
    .directive('arPager', require('./PagerDirective'))
    .directive('arMultiselect', require('./MultiselectDirective'))
    .directive('arMultiselectPopup', require('./MultiselectPopupDirective'))
    .directive('arToggleSlide', require('./ToggleSlideDirective'))
    .directive('arSearchBox', require('./SearchBoxDirective'))
    .directive('arRangeFacet', require('./RangeFacetDirective'))
    .directive('arGraphLine', require('./GraphLineDirective'))
    .directive('arGraphPie', require('./GraphPieDirective'));
})();
