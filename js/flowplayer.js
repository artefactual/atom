// $Id: flowplayer.js 12267 2012-09-05 02:12:40Z sevein $

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
                  wmode: 'transparent'
                },

                // Flowplayer configuration
                {
                  clip:
                    {
                      autoPlay: false
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
