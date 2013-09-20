(function ($)
  {
    Qubit.updateCheck = Qubit.updateCheck || {};

    Drupal.behaviors.updateCheck = {
      attach: function (context)
        {
          var showNotification = function (version)
            {
              // Show notification only when server version is greater
              if (-1 == version_compare(Qubit.updateCheck.currentVersion, version))
              {
                $('<span>' + Qubit.updateCheck.notificationMessage.replace(/\%\d+\%/g, version) + '</span>').prependTo('#update-check').parent().show();
              }
            };

          var version = YAHOO.util.Cookie.get('update_checked');

          if (version)
          {
            showNotification(version);

            return;
          }

          $.ajax({
            data: Qubit.updateCheck.data,
            dataType: 'jsonp',
            jsonpCallback: 'updateCheck',
            timeout: 20000,
            type: 'GET',
            url: Qubit.updateCheck.url,
            success: function(data)
              {
                showNotification(data.version);

                YAHOO.util.Cookie.set('update_checked', data.version, { path: Qubit.updateCheck.cookiePath });
              }
          });
        }
    }

    // See: https://gist.github.com/alexey-bass/1115557
    function version_compare(left, right)
    {
      if (typeof left + typeof right != 'stringstring')
      {
        return false;
      }

      var a = left.split('.');
      var b = right.split('.');
      var i = 0;
      var len = Math.max(a.length, b.length);

      for (; i < len; i++)
      {
        if ((a[i] && !b[i] && parseInt(a[i]) > 0) || (parseInt(a[i]) > parseInt(b[i])))
        {
          return 1;
        }
        else if ((b[i] && !a[i] && parseInt(b[i]) > 0) || (parseInt(a[i]) < parseInt(b[i])))
        {
          return -1;
        }
      }

      return 0;
    }
  })
(jQuery);
