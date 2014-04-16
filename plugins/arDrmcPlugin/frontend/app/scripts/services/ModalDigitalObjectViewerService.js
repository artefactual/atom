'use strict';

module.exports = function ($modal, SETTINGS) {
  var configuration = {
    templateUrl: SETTINGS.viewsPath + '/modals/digital-object-viewer.html',
    backdrop: true,
    resolve: {},
    controller: 'DigitalObjectViewerCtrl'
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

  this.open = function (files) {
    configuration.resolve.files = function () {
      // Use only the first three items in the array
      if (files.length > 3) {
        files = files.slice(0, 3);
      }
      return files;
    };
    return $modal.open(configuration);
  };
};
