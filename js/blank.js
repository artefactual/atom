(function ($)
  {
    Drupal.behaviors.blank = {
      attach: function (context)
        {
          $('section, .section, .field', context)
            .filter(function ()
              {
                return !$('input, #treeView, > .search-results, #imageflow', this).length
                  && !jQuery.trim($(':not(h2, h2 *, h3, h3 *, h4, h4 *)', this)
                    .contents()
                    .filter(function ()
                      {
                        return 3 == this.nodeType;
                      })
                    .text());
              })
            .remove();
        } };
  })(jQuery);
