(function ($)
  {
    Drupal.behaviors.flowplayer = {
      attach: function (context)
        {
          $('.flowplayer', context).each(function ()
            {
              flowplayer(

                // DOM Object
                this,

                // Flash configuration
                {
                  src: Qubit.relativeUrlRoot + '/vendor/flowplayer/flowplayer-3.1.5.swf',
                  wmode: 'transparent',
                  width: 320,
                  height: 240
                },

                // Flowplayer configuration
                {
                  clip:
                    {
                      autoPlay: false,
                      scale: 'orig'
                    },
                  canvas:
                    {
                      backgroundColor: '#000000',
                      backgroundGradient: 'medium',
                      borderRadius: 10
                    }
                });

            });
        }};
  })(jQuery);
