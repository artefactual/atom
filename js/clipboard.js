(function($)
{
  'use strict';

  function Clipboard(element)
  {
    this.$element = element;
    this.$menuButton = this.$element.find('> button');
    this.$menuHeaderCount = this.$element.find('.top-dropdown-header > span');
    this.onClipboardPage = $('body').is('.clipboard.view');

    this.storage = localStorage;
    this.types = ['informationObject', 'actor', 'repository'];
    this.initialItems = {'informationObject': [], 'actor': [], 'repository': []};
    this.items = JSON.parse(this.storage.getItem('clipboard')) || this.initialItems;
    this.exportTokens = JSON.parse(this.storage.getItem('exportTokens')) || [];
    this.init();
  };

  Clipboard.prototype = {

    init: function()
    {
      // Listeners added to the document to affect elements added dynamically
      $(document).on('click', 'button.clipboard', $.proxy(this.toggle, this, true));
      $(document).on('click', 'button.clipboard-wide', $.proxy(this.toggle, this, false));
      $(document).on('click', 'button#clipboard-clear, li#node_clearClipboard a', $.proxy(this.clear, this));
      $(document).on('click', 'a#clipboard-save, li#node_saveClipboard a', $.proxy(this.save, this));
      $(document).on('click', 'button#clipboard-send', $.proxy(this.send, this));
      $(document).on('submit', '#clipboard-load-form', $.proxy(this.load, this));
      $(document).on('submit', '#clipboard-export-form', $.proxy(this.export, this));

      this.updateCounts();

      if (this.onClipboardPage)
      {
        this.loadClipboardContent();
      }
      else
      {
        this.updateAllButtons();
      }

      this.checkExports();
    },
    load: function(event)
    {
      event.preventDefault();

      var $form = $(event.target);
      var mode = $form.find('select#mode').val();
      var loadType = $(document.activeElement).attr("name");

      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        cache: false,
        data: $form.serialize(),
        context: this,
        success: function(data)
        {
          if (mode === 'merge')
          {
            this.types.map(function(type)
            {
              if (data.clipboard[type])
              {
                data.clipboard[type].map(function(slug)
                {
                  if (this.items[type].indexOf(slug) === -1)
                  {
                    this.items[type].push(slug);
                  }
                }, this);
              }
            }, this);
          }
          else if (mode === 'replace')
          {
            this.items = data.clipboard;
          }

          this.storage.setItem('clipboard', JSON.stringify(this.items));
          this.updateCounts();
          this.showAlert(data.success, 'alert-info');

          if (loadType == "loadView") {
            window.location.href = "/clipboard/view";
          }
        },
        error: function(xhr)
        {
          var data = JSON.parse(xhr.responseText);
          this.showAlert(data.error, 'alert-error');
        }
      });
    },
    loadClipboardContent: function()
    {
      var url = new URL(window.location.href);
      var type = url.searchParams.get('type');

      if (!type || !this.types.includes(type))
      {
        type = 'informationObject';
      }

      // Get clipboard content, use post instead of get to
      // reduce URL length and simplify cache proxy config.
      $.ajax({
        url: url,
        type: 'POST',
        cache: false,
        data: { slugs: this.items[type] },
        context: this,
        success: function(data)
        {
          // Replace page content
          $('body > #wrapper').replaceWith($(data).filter('#wrapper'));

          // Attach expander from qubit.js and other behaviors to new content
          Drupal.attachBehaviors('#wrapper');

          this.updateAllButtons();
        },
        error: function()
        {
          this.showAlert(this.$element.data('load-alert-message'), 'alert-error');
        }
      });
    },
    save: function(event)
    {
      event.preventDefault();

      // Avoid request if there are no slugs in the clipboard
      if (
        this.items['informationObject'].length === 0 &&
        this.items['actor'].length === 0 &&
        this.items['repository'].length === 0
      )
      {
        return;
      }

      $.ajax({
        url: $(event.target).attr('href'),
        type: 'POST',
        cache: false,
        data: { slugs: this.items },
        context: this,
        success: function(data)
        {
          this.showAlert(data.success, 'alert-info');
        },
        error: function(xhr)
        {
          var data = JSON.parse(xhr.responseText);
          this.showAlert(data.error, 'alert-error');
        }
      });
    },
    send: function(event)
    {
      var $sendButton = $(event.target);

      // Avoid request if there are no slugs in the clipboard
      if (
        this.items['informationObject'].length === 0 &&
        this.items['actor'].length === 0 &&
        this.items['repository'].length === 0
      )
      {
        this.showAlert($sendButton.data('empty-message'), 'alert-error');

        return;
      }

      let $form = $("<form />", {
        "id": "sendForm",
        "action": $sendButton.data("url"),
        "method": $sendButton.data("method"),
      });

      // Generate clipboard send data
      let $baseUrl = $("<input />", {
        "type": "hidden",
        "name": "base_url",
        "value": $sendButton.data('site-base-url')
      });

      $form.append($baseUrl);

      if (this.items['informationObject'].length !== 0)
      {
        let $informationobjectSlugs = $("<input />", {
          "type": "hidden",
          "name": "information_object_slugs",
          "value": JSON.stringify(this.items['informationObject'])
        });
        $form.append($informationobjectSlugs);
      }

      if (this.items['actor'].length !== 0)
      {
        let $actorSlugs = $("<input />", {
          "type": "hidden",
          "name": "actor_slugs",
          "value": JSON.stringify(this.items['actor'])
        });
        $form.append($actorSlugs);
      }

      if (this.items['repository'].length !== 0)
      {
        let $repositorySlugs = $("<input />", {
          "type": "hidden",
          "name": "repository_slugs",
          "value": JSON.stringify(this.items['repository'])
        });
        $form.append($repositorySlugs);
      }

      // Show sending alert and assign it to a variable
      var $sendingAlert = this.showAlert($sendButton.data('message'), 'alert-info');

      $form.appendTo(document.body);
      $form.submit();
    },
    export: function(event)
    {
      event.preventDefault();

      var $form = $(event.target);
      var type = $form.find('select#type').val();

      // Avoid request if there are no slugs for the type
      if (this.items[type].length === 0)
      {
        this.showAlert(this.$element.data('export-alert-message'), 'alert-error');

        return;
      }

      // Merge form data and slugs
      var data = $form.serializeArray();
      this.items[type].map(function(slug)
      {
        data.push({name: 'slugs[]', value: slug});
      });

      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        cache: false,
        data: data,
        context: this,
        success: function(responseData)
        {
          this.showAlert(responseData.success, 'alert-info');

          // Unauthenticated users will get a token to check the export
          if (responseData.token)
          {
            this.exportTokens.push(responseData.token);
            this.storage.setItem('exportTokens', JSON.stringify(this.exportTokens));
          }
        },
        error: function(xhr)
        {
          var data = JSON.parse(xhr.responseText);
          this.showAlert(data.error, 'alert-error');
        }
      });
    },
    checkExports: function()
    {
      if (this.exportTokens.length === 0)
      {
        return;
      }

      // Use post instead of get to send the tokens
      // in the body and simplify cache proxy config.
      $.ajax({
        url: this.$element.data('export-check-url'),
        type: 'POST',
        cache: false,
        data: { tokens: this.exportTokens },
        context: this,
        success: function(data)
        {
          // Show status alerts
          if (data.alerts)
          {
            data.alerts.map(function(alert)
            {
              this.showAlert(alert.message, 'alert-' + alert.type, alert.deleteUrl);
            }, this);
          }

          // Clear missing tokens
          if (data.missingTokens)
          {
            data.missingTokens.map(function(token)
            {
              var index = this.exportTokens.indexOf(token);

              if (index !== -1)
              {
                this.exportTokens.splice(index, 1);
              }
            }, this);

            this.storage.setItem('exportTokens', JSON.stringify(this.exportTokens));
          }
        },
        error: function(xhr)
        {
          var data = JSON.parse(xhr.responseText);
          this.showAlert(data.error, 'alert-error');
        }
      });
    },
    toggle: function(reloadTooltip, event)
    {
      if (typeof event.preventDefault === 'function')
      {
        event.preventDefault();
      }

      var $button = $(event.target);

      if (reloadTooltip)
      {
        $button.tooltip('hide');
      }

      // Load items from local storage in case activity
      // in another tab has changed the content
      this.items = JSON.parse(this.storage.getItem("clipboard")) || this.initialItems;

      var type = $button.data('clipboard-type');
      var slug = $button.data('clipboard-slug');
      var index = this.items[type].indexOf(slug);

      if (index === -1)
      {
        this.items[type].push(slug);
        this.updateButton($button, true, reloadTooltip);
      }
      else
      {
        this.items[type].splice(index, 1);
        this.updateButton($button, false, reloadTooltip);
      }

      this.storage.setItem('clipboard', JSON.stringify(this.items));
      this.updateCounts();
    },
    clear: function(event)
    {
      event.preventDefault();

      this.showRemoveAlert();

      var $target = $(event.target);
      var type = $target.data('clipboard-type');

      if (type && this.types.includes(type))
      {
        this.items[type] = [];
      }
      else
      {
        this.items = this.initialItems;
      }

      this.storage.setItem('clipboard', JSON.stringify(this.items));

      this.updateCounts();
      this.updateAllButtons();
    },
    updateButton: function($button, added, reloadTooltip)
    {
      // If previous and current status don't match,
      // change status, tooltip and button content
      if ((!$button.hasClass('added') && added)
        || ($button.hasClass('added') && !added))
      {
        // Show alert when removing
        if (!added)
        {
          this.showRemoveAlert();
        }

        $button.toggleClass('added');

        var label = $button.attr('data-title');
        var altLabel = $button.attr('data-alt-title');

        $button.attr('data-alt-title', label);
        $button.attr('data-title', altLabel);
        $button.text(altLabel);

        // Fix tooltip only in small buttons
        if (reloadTooltip)
        {
          $button.tooltip()
            .attr('data-original-title', altLabel)
            .tooltip('fixTitle');
        }
      }
    },
    updateCounts: function()
    {
      var iosCount = this.items['informationObject'].length;
      var actorsCount = this.items['actor'].length;
      var reposCount = this.items['repository'].length;
      var totalCount = iosCount + actorsCount + reposCount;

      // Menu button count
      var $buttonSpan = this.$menuButton.find('> span');
      if (!$buttonSpan.length && totalCount > 0)
      {
        this.$menuButton.append('<span>' + totalCount + '</span>');
      }
      else if (totalCount > 0)
      {
        $buttonSpan.text(totalCount);
      }
      else if ($buttonSpan.length)
      {
        $buttonSpan.remove();
      }

      // Menu dropdown header count
      var countText = this.$menuHeaderCount.data('information-object-label');
      countText += ' count: ' + iosCount + '<br />';
      countText += this.$menuHeaderCount.data('actor-object-label');
      countText += ' count: ' + actorsCount + '<br />';
      countText += this.$menuHeaderCount.data('repository-object-label');
      countText += ' count: ' + reposCount + '<br />';

      this.$menuHeaderCount.html(countText);
    },
    updateAllButtons: function()
    {
      $('button.clipboard').tooltip({'placement': 'bottom', 'container': 'body'});

      var self = this;

      $('button[class*="clipboard"]').each(function()
      {
        var $button = $(this);
        var type = $button.data('clipboard-type');
        var slug = $button.data('clipboard-slug');
        var reloadTooltip = $button.hasClass('clipboard');
        var added = self.items[type].indexOf(slug) !== -1;

        self.updateButton($button, added, reloadTooltip);
      });
    },
    showAlert: function(message, type, deleteUrl)
    {
      if (!type)
      {
        type = '';
      }

      var $alert = $('<div class="alert ' + type + '">');

      if (deleteUrl)
      {
        $alert.append('<a href="' + deleteUrl + '"><button type="button" class="close">&times;</button></a>');
      }
      else
      {
        $alert.append('<button type="button" data-dismiss="alert" class="close">&times;</button>');
      }

      $alert.append(message).prependTo($('#wrapper.container'));

      return $alert;
    },
    showRemoveAlert: function()
    {
      // Show remove alert only in clipboard page if it is not already added
      if (this.onClipboardPage && $('#wrapper.container > .alert-clipboard-remove').length == 0)
      {
        this.showAlert(this.$element.data('delete-alert-message'), 'alert-clipboard-remove');
      }
    }
  };

  $(function()
  {
    var $clipboard = $('#clipboard-menu');

    if ($clipboard.length)
    {
      $clipboard.data('clipboard', new Clipboard($clipboard));
    }
  });

})(jQuery);
