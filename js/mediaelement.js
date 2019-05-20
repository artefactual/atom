(function ($)
  {
    Drupal.behaviors.mediaelement = {
      attach: function (context)
        {
          $('.mediaelement-player', context).each(function ()
            {
              $(this).mediaelementplayer({
                pluginPath: Qubit.relativeUrlRoot + '/vendor/mediaelement/',
                renderers: ['html5', 'flash_video'],
                alwaysShowControls: true,
                stretching: 'responsive'
              });
            });
        }};
  })(jQuery);
