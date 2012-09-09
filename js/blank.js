// $Id: select.js 3583 2009-09-29 00:42:32Z jablko $

(function ($)
  {
    Drupal.behaviors.blank = {
      attach: function (context)
        {
          $('.section, .field', context)
            .filter(function ()
              {
                return !$('input, #treeView, > .search-results, #imageflow, .institution', this).length
                  && !jQuery.trim($(':not(h2, h2 *, h3, h3 *)', this)
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
