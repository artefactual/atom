Drupal.behaviors.button = {
  attach: function (context)
    {
      $('select', context).each(function ()
        {
          var button = new YAHOO.widget.Button({
            container: this.parentNode,
            label: $('option:selected', this).text(),
            menu: this,
            selectedMenuItem: $('option:selected', this).get(0),
            type: 'menu' });

          // Change the button label when a menu item is clicked.  Do not use the
          // selectedMenuItemChange event because it fires before a menu item is
          // clicked.
          button.getMenu().subscribe('click', function (type, args)
            {
              // If the target of the event was a MenuItem instance, it will be
              // passed back as the second argument
              if (args[1] != null)
              {
                button.set('label', args[1].cfg.getProperty('text'));
              }
            });
        });
    } };
