Qubit.contextMenu = Qubit.contextMenu || {};

Drupal.behaviors.contextMenu = {
  attach: function (context)
    {
      $('#treeView').click(function (event)
        {
          function getTarget(target)
          {
            if (target.tagName.toLowerCase() == 'a' && target.className == 'ygtvlabel')
            {
              return target;
            }

            if (target.parentNode && target.parentNode.nodeType == 1)
            {
              return getTarget(target.parentNode);
            }
          }

          var target = getTarget(event.target);
          if (!target)
          {
            return false;
          }

          var menu = new YAHOO.widget.Menu('menu', {
            itemData: [
              { text: 'Add' },
              { text: 'Delete' },
              { text: 'Edit' },
              { text: 'List' },
              { text: 'Show' }],
            lazyLoad: true,
            x: event.pageX,
            y: event.pageY });

          menu.show();

          return false;
        });
    } };
