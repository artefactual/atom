(function () {

  'use strict';

  var angular = require('angular');

  module.exports = angular.module('momaApp.directives', [])
    .directive('arContextBrowser', require('./ContextBrowserDirective'))
    .directive('arMomaToggle', require('./MomaToggleDirective'))
    .directive('arSortHeader', require('./SortHeader'))
    .directive('arFacet', require('./Facet'))
    .directive('arPager', require('./PagerDirective'));

})();
