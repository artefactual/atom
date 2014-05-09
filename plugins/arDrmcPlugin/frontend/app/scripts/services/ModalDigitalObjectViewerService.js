'use strict';

module.exports = function ($modal, SETTINGS) {

  var configuration = {
    templateUrl: SETTINGS.viewsPath + '/modals/digital-object-viewer.html',
    backdrop: true,
    resolve: {},
    windowClass: 'fullscreen-modal digital-object-viewer',
    controller: 'DigitalObjectViewerCtrl'
  };

  this.open = function (files, index) {
    configuration.resolve.files = function () {
      // Make sure that we convert into array when files is just one object
      if (!angular.isArray(files)) {
        files = new Array(files);
      }
      return files;
    };
    configuration.resolve.index = function () {
      if (angular.isUndefined(index)) {
        index = 0;
      }
      return index;
    };
    return $modal.open(configuration);
  };

  // Media types
  this.mediaTypes = {
    135: {
      class: 'audio',
      name: 'Audio',
      templateUrl: SETTINGS.viewsPath + '/partials/digital-object-preview.audio.html'
    },
    136: {
      class: 'image',
      name: 'Image',
      templateUrl: SETTINGS.viewsPath + '/partials/digital-object-preview.image.html'
    },
    137: {
      class: 'text',
      name: 'Text',
      templateUrl: SETTINGS.viewsPath + '/partials/digital-object-preview.text.html'
    },
    138: {
      class: 'video',
      name: 'Video',
      templateUrl: SETTINGS.viewsPath + '/partials/digital-object-preview.video.html'
    },
    139: {
      class: 'other',
      name: 'Other',
      templateUrl: SETTINGS.viewsPath + '/partials/digital-object-preview.other.html'
    }
  };

};
