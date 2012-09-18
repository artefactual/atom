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
