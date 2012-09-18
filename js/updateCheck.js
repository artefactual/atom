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

    function version_compare(v1, v2)
    {
      splitVersion = function (v)
        {
          return ('' + v).split('.');
        };

      v1 = splitVersion(v1);
      v2 = splitVersion(v2);

      var compare = 0;

      for (i = 0; i < Math.max(v1.length, v2.length); i++)
      {
        if (v1[i] == v2[i])
        {
          continue;
        }

        v1[i] = parseInt(v1[i]);
        v2[i] = parseInt(v2[i]);

        if (v1[i] < v2[i])
        {
          compare = -1;

          break;
        }
        else if(v1[i] > v2[i])
        {
          compare = 1;

          break;
        }
      }

      return compare;
    }
  })(jQuery);
