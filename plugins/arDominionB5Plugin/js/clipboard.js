import Tooltip from "bootstrap/js/dist/tooltip";

(function ($) {
  "use strict";

  class Clipboard {
    constructor(element) {
      this.$element = element;
      this.$menuHeaderCount = this.$element.closest("li").find("#counts-block");
      this.onClipboardPage = $("body").is(".clipboard.view");

      this.storage = localStorage;
      this.types = ["informationObject", "actor", "repository"];
      this.initialItems = { informationObject: [], actor: [], repository: [] };
      this.items =
        JSON.parse(this.storage.getItem("clipboard")) || this.initialItems;
      this.exportTokens =
        JSON.parse(this.storage.getItem("exportTokens")) || [];

      this.init();
    }

    init() {
      // Listeners added to the document to affect elements added dynamically
      $(document).on("click", "button.clipboard", this.toggle.bind(this));
      $(document).on(
        "click",
        "button#clipboard-clear, li#node_clearClipboard a",
        this.clear.bind(this)
      );
      $(document).on(
        "click",
        "a#clipboard-save, li#node_saveClipboard a",
        this.save.bind(this)
      );
      $(document).on("click", "button#clipboard-send", this.send.bind(this));
      $(document).on("submit", "#clipboard-load-form", this.load.bind(this));
      $(document).on(
        "submit",
        "#clipboard-export-form",
        this.export.bind(this)
      );
      $(document).on(
        "click",
        ".clipboard-all, .clipboard-none",
        this.toggleAll.bind(this)
      );

      this.updateCounts();

      if (this.onClipboardPage) {
        this.loadClipboardContent();
      } else {
        this.updateAllButtons();
      }

      this.checkExports();
    }

    load(event) {
      event.preventDefault();

      var $form = $(event.target);
      var mode = $form.find("select#mode").val();
      var loadType = $(document.activeElement).attr("name");

      $.ajax({
        url: $form.attr("action"),
        type: "POST",
        cache: false,
        data: $form.serialize(),
        context: this,
        success: function (data) {
          if (mode === "merge") {
            this.types.map(function (type) {
              if (data.clipboard[type]) {
                data.clipboard[type].map(function (slug) {
                  if (this.items[type].indexOf(slug) === -1) {
                    this.items[type].push(slug);
                  }
                }, this);
              }
            }, this);
          } else if (mode === "replace") {
            this.items = data.clipboard;
          }

          this.storage.setItem("clipboard", JSON.stringify(this.items));
          this.updateCounts();
          this.showAlert(data.success, "alert-info");

          if (loadType == "loadView") {
            window.location.href = "/clipboard/view";
          }
        },
        error: function (xhr) {
          var data = JSON.parse(xhr.responseText);
          this.showAlert(data.error, "alert-danger");
        },
      });
    }

    loadClipboardContent() {
      var url = new URL(window.location.href);
      var type = url.searchParams.get("type");

      if (!type || !this.types.includes(type)) {
        type = "informationObject";
      }

      // Get clipboard content, use post instead of get to
      // reduce URL length and simplify cache proxy config.
      $.ajax({
        url: url,
        type: "POST",
        cache: false,
        data: { slugs: this.items[type] },
        context: this,
        success: function (data) {
          // Replace page content
          $("body > #wrapper").replaceWith($(data).filter("#wrapper"));

          // Attach behaviors to new content
          Drupal.attachBehaviors("#wrapper");

          this.updateAllButtons();
        },
        error: function () {
          this.showAlert(
            this.$element.data("load-alert-message"),
            "alert-danger"
          );
        },
      });
    }

    save(event) {
      event.preventDefault();

      // Avoid request if there are no slugs in the clipboard
      if (
        this.items["informationObject"].length === 0 &&
        this.items["actor"].length === 0 &&
        this.items["repository"].length === 0
      ) {
        return;
      }

      $.ajax({
        url: $(event.target).closest("a").attr("href"),
        type: "POST",
        cache: false,
        data: { slugs: this.items },
        context: this,
        success: function (data) {
          this.showAlert(data.success, "alert-info");
        },
        error: function (xhr) {
          var data = JSON.parse(xhr.responseText);
          this.showAlert(data.error, "alert-danger");
        },
      });
    }

    send(event) {
      var $sendButton = $(event.target);

      // Avoid request if there are no slugs in the clipboard
      if (
        this.items["informationObject"].length === 0 &&
        this.items["actor"].length === 0 &&
        this.items["repository"].length === 0
      ) {
        this.showAlert($sendButton.data("empty-message"), "alert-danger");

        return;
      }

      let $form = $("<form />", {
        id: "sendForm",
        action: $sendButton.data("url"),
        method: $sendButton.data("method"),
      });

      // Generate clipboard send data
      let $baseUrl = $("<input />", {
        type: "hidden",
        name: "base_url",
        value: $sendButton.data("site-base-url"),
      });

      $form.append($baseUrl);

      if (this.items["informationObject"].length !== 0) {
        let $informationobjectSlugs = $("<input />", {
          type: "hidden",
          name: "information_object_slugs",
          value: JSON.stringify(this.items["informationObject"]),
        });
        $form.append($informationobjectSlugs);
      }

      if (this.items["actor"].length !== 0) {
        let $actorSlugs = $("<input />", {
          type: "hidden",
          name: "actor_slugs",
          value: JSON.stringify(this.items["actor"]),
        });
        $form.append($actorSlugs);
      }

      if (this.items["repository"].length !== 0) {
        let $repositorySlugs = $("<input />", {
          type: "hidden",
          name: "repository_slugs",
          value: JSON.stringify(this.items["repository"]),
        });
        $form.append($repositorySlugs);
      }

      // Show sending alert and assign it to a variable
      var $sendingAlert = this.showAlert(
        $sendButton.data("message"),
        "alert-info"
      );

      $form.appendTo(document.body);
      $form.submit();
    }

    export(event) {
      event.preventDefault();

      var $form = $(event.target);
      var type = $form.find("select#type").val();

      // Avoid request if there are no slugs for the type
      if (this.items[type].length === 0) {
        this.showAlert(
          this.$element.data("export-alert-message"),
          "alert-danger"
        );

        return;
      }

      // Merge form data and slugs
      var data = $form.serializeArray();
      this.items[type].map(function (slug) {
        data.push({ name: "slugs[]", value: slug });
      });

      $.ajax({
        url: $form.attr("action"),
        type: "POST",
        cache: false,
        data: data,
        context: this,
        success: function (responseData) {
          this.showAlert(responseData.success, "alert-info");

          // Unauthenticated users will get a token to check the export
          if (responseData.token) {
            this.exportTokens.push(responseData.token);
            this.storage.setItem(
              "exportTokens",
              JSON.stringify(this.exportTokens)
            );
          }
        },
        error: function (xhr) {
          var data = JSON.parse(xhr.responseText);
          this.showAlert(data.error, "alert-danger");
        },
      });
    }

    checkExports() {
      if (this.exportTokens.length === 0) {
        return;
      }

      // Use post instead of get to send the tokens
      // in the body and simplify cache proxy config.
      $.ajax({
        url: this.$element.data("export-check-url"),
        type: "POST",
        cache: false,
        data: { tokens: this.exportTokens },
        context: this,
        success: function (data) {
          // Show status alerts
          if (data.alerts) {
            data.alerts.map(function (alert) {
              this.showAlert(
                alert.message,
                "alert-" + alert.type,
                alert.deleteUrl
              );
            }, this);
          }

          // Clear missing tokens
          if (data.missingTokens) {
            data.missingTokens.map(function (token) {
              var index = this.exportTokens.indexOf(token);

              if (index !== -1) {
                this.exportTokens.splice(index, 1);
              }
            }, this);

            this.storage.setItem(
              "exportTokens",
              JSON.stringify(this.exportTokens)
            );
          }
        },
        error: function (xhr) {
          var data = JSON.parse(xhr.responseText);
          this.showAlert(data.error, "alert-danger");
        },
      });
    }

    toggle(event) {
      if (typeof event.preventDefault === "function") {
        event.preventDefault();
      }

      // Load items from local storage in case activity
      // in another tab has changed the content
      this.items =
        JSON.parse(this.storage.getItem("clipboard")) || this.initialItems;

      var $button = $(event.target).closest("button");
      var type = $button.data("clipboard-type");
      var slug = $button.data("clipboard-slug");
      var index = this.items[type].indexOf(slug);

      if (index === -1) {
        this.items[type].push(slug);
        this.updateButton($button, true);
      } else {
        this.items[type].splice(index, 1);
        this.updateButton($button, false);
      }

      this.storage.setItem("clipboard", JSON.stringify(this.items));
      this.updateCounts();
    }

    toggleAll(event) {
      event.preventDefault();
      var add = $(event.target).hasClass("clipboard-all");
      $("button.clipboard").each((_, button) => {
        var $button = $(button);
        var added = $button.hasClass("active");
        if ((!added && add) || (added && !add)) $button.trigger("click");
      });
    }

    clear(event) {
      event.preventDefault();

      this.showRemoveAlert();

      var $target = $(event.target);
      var type = $target.data("clipboard-type");

      if (type && this.types.includes(type)) {
        this.items[type] = [];
      } else {
        this.items = this.initialItems;
      }

      this.storage.setItem("clipboard", JSON.stringify(this.items));

      this.updateCounts();
      this.updateAllButtons();
    }

    updateButton($button, added) {
      var showTooltip = $button.data("tooltip") != undefined;

      // If previous and current status don't match
      if (
        (!$button.hasClass("active") && added) ||
        ($button.hasClass("active") && !added)
      ) {
        // Update button
        var label = $button.data("title");
        var altLabel = $button.data("alt-title");
        $button.data("alt-title", label);
        $button.data("title", altLabel);
        $button.find("span").text(altLabel);
        $button.toggleClass("active");

        // Remove tooltip
        if (showTooltip) {
          Tooltip.getOrCreateInstance($button).dispose();
        }

        // Show alert when removing
        if (!added) {
          this.showRemoveAlert();
        }
      }

      // Add tooltip
      if (showTooltip) {
        Tooltip.getOrCreateInstance($button, {
          title: $button.data("title"),
          placement: "left",
        });
      }
    }

    updateCounts() {
      var iosCount = this.items["informationObject"].length;
      var actorsCount = this.items["actor"].length;
      var reposCount = this.items["repository"].length;
      var totalCount = iosCount + actorsCount + reposCount;

      // Menu button count
      var $buttonSpan = this.$element.find("> span.clipboard-count");
      if (!$buttonSpan.length && totalCount > 0) {
        this.$element.append(
          '<span class="clipboard-count position-absolute top-0 start-0' +
            ' badge rounded-pill bg-primary">' +
            totalCount +
            '<span class="visually-hidden">' +
            this.$element.data("total-count-label") +
            "</span></span>"
        );
      } else if (totalCount > 0) {
        $buttonSpan.text(totalCount);
      } else if ($buttonSpan.length) {
        $buttonSpan.remove();
      }

      // Menu dropdown header count
      var countText = this.$menuHeaderCount.data("information-object-label");
      countText += " count: " + iosCount + "<br />";
      countText += this.$menuHeaderCount.data("actor-object-label");
      countText += " count: " + actorsCount + "<br />";
      countText += this.$menuHeaderCount.data("repository-object-label");
      countText += " count: " + reposCount + "<br />";

      this.$menuHeaderCount.html(countText);
    }

    updateAllButtons() {
      var self = this;

      $("button.clipboard").each(function () {
        var $button = $(this);
        var type = $button.data("clipboard-type");
        var slug = $button.data("clipboard-slug");
        var added = self.items[type].indexOf(slug) !== -1;

        self.updateButton($button, added);
      });
    }

    showAlert(message, type, deleteUrl) {
      if (!type) {
        type = "";
      }

      var $alert = $(
        '<div class="alert ' +
          type +
          ' alert-dismissible fade show" role="alert">'
      ).append(message);
      var closeButton =
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="' +
        this.$element.data("alert-close") +
        '"></button>';

      if (deleteUrl) {
        $alert.append('<a href="' + deleteUrl + '">' + closeButton + "</a>");
      } else {
        $alert.append(closeButton);
      }

      $alert.prependTo($("body > #wrapper"));
      window.scrollTo({ top: 0 });

      return $alert;
    }

    showRemoveAlert() {
      // Show remove alert only in clipboard page if it is not already added
      if (
        this.onClipboardPage &&
        $("body > #wrapper > .alert-clipboard-remove").length == 0
      ) {
        this.showAlert(
          this.$element.data("delete-alert-message"),
          "alert-danger alert-clipboard-remove"
        );
      }
    }
  }

  $(() => {
    var $clipboard = $("#clipboard-menu");
    if ($clipboard.length) new Clipboard($clipboard);
  });
})(jQuery);
