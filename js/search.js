// $Id: search.js 5112 2010-01-29 17:05:18Z sevein $

Drupal.behaviors.search = {
  attach: function (context)
    {
      $('input.search', context).each(function ()
        {
          var input = this;

          $(this.form).hide();

          $(':submit', this.form)
            .click(function (event)
              {
                event.preventDefault();

                $(input).val($('#search-sidebar :text').val());

                $(input.form).submit();
              })
            .insertBefore('#search-sidebar :submit');
        });
    } };
