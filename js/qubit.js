var Qubit = Qubit || {};

// Usage: log('inside coolFunc',this,arguments);
// http://paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function()
  {
    log.history = log.history || [];
    log.history.push(arguments);

    if (this.console)
    {
      console.log( Array.prototype.slice.call(arguments) );
    }
  };

// jQuery expander
Drupal.behaviors.expander = {
  attach: function (context)
    {
      jQuery('div.field:not(:has(div.field)) > div:not(:has(ul, li))')
        .expander({
          slicePoint: 255,
          expandText: '&raquo;',
          expandPrefix: '... ',
          userCollapseText: '&laquo;',
          widow: 4,
          expandEffect: 'show'
        });
    }
  };
