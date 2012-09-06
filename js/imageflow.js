// $Id: imageflow.js 6303 2010-04-15 18:21:32Z jablko $

(function ($)
  {
    Drupal.behaviors.imageflow = {
      attach: function (context)
        {
          $('.imageflow', context).each(function ()
            {
              new ImageFlow().init({
                opacity: true,
                reflectionP: 0,
                reflections: false });
            });
        } };
  })(jQuery);
