(function ($) {
  "use strict";

  Drupal.behaviors.mediaelement = {
    attach: function (context) {
      $("video, audio", context).each(function () {
        $(this).mediaelementplayer({
          pluginPath: "node_modules/mediaelement/build/",
          renderers: ["html5", "flash_video"],
          alwaysShowControls: true,
          stretching: "responsive",
        });
      });
    },
  };
})(jQuery);
