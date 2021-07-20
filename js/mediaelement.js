(function ($)
  {
    Drupal.behaviors.mediaelement = {
      attach: function (context)
        {
          $('.mediaelement-player', context).each(function ()
            {
              $(this).mediaelementplayer({
                pluginPath: '/vendor/mediaelement/',
                renderers: ['html5', 'flash_video'],
                alwaysShowControls: true,
                stretching: 'responsive'
              });
            });
        }};
  })(jQuery);
