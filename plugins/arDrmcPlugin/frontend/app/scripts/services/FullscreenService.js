'use strict';

module.exports = function ($document) {

  var document = $document.get(0);

  return {

    all: function () {
      this.enable(document.documentElement);
    },

    enable: function (element) {
      if (element.requestFullScreen) {
        element.requestFullScreen();
      } else if (element.mozRequestFullScreen) {
        element.mozRequestFullScreen();
      } else if (element.webkitRequestFullScreen) {
        element.webkitRequestFullScreen();
      }
    },

    cancel: function () {
      if (document.cancelFullScreen) {
        document.cancelFullScreen();
      } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
      } else if (document.webkitCancelFullScreen) {
        document.webkitCancelFullScreen();
      }
    },

    isEnabled: function () {
      return document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement;
    }

  };

};
