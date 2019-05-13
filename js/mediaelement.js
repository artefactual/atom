(function ($)
  {
    $(document).ready(function() {
      $('.mediaelement-player').mediaelementplayer({
        pluginPath: Qubit.relativeUrlRoot + '/vendor/mediaelement/',
        renderers: ['html5', 'flash_video'],
        alwaysShowControls: true,
        stretching: 'responsive'
      });
    });
  }
)(jQuery);
