(function ($) {
  "use strict";

  class FullWidthTreeView {
    constructor(element) {
      this.$element = element;
      this.$treeViewConfig = $("#fullwidth-treeview-configuration");
      this.collectionUrl = this.$treeViewConfig.data("collection-url");
      this.itemsPerPage = this.$treeViewConfig.data("items-per-page");
      this.dndEnabled = this.$treeViewConfig.data("enable-dnd") == "yes";
      this.pathToApi = "/informationobject/fullWidthTreeView";
      this.$fwTreeView = $('<div id="fullwidth-treeview"></div>');
      this.$fwTreeViewRow = $('<div id="fullwidth-treeview-row"></div>');
      this.$mainHeader = $("#main-column h1").first();
      this.$moreButton = $("#fullwidth-treeview-more-button").hide();
      this.$resetButton = $("#fullwidth-treeview-reset-button").hide();
      this.pager = new Qubit.TreeviewPager(
        this.itemsPerPage,
        this.$fwTreeView,
        this.collectionUrl + this.pathToApi
      );
      this.treeViewCollapseEnabled =
        this.$treeViewConfig.data("collapse-enabled") == "yes";

      // Add tree-view divs after main header, animate and allow resize
      this.$mainHeader.after(
        this.$fwTreeViewRow
          .append(this.$fwTreeView)
          .animate({ height: "200px" }, 500)
          .resizable({ handles: "s" })
      );

      this.addTreeviewToAccordion();
      this.addButtonSection();
      this.$accordionWrapper.after(this.$buttonSection);

      // Keep track of which syncs have been initiated
      this.syncInitiated = {};

      // Declare jsTree options
      this.options = {
        plugins: ["types", "dnd"],
        types: Qubit.treeviewTypes,
        dnd: {
          // Drag and drop configuration, disable:
          // - Node copy on drag
          // - Load nodes on hover while dragging
          // - Multiple node drag
          // - Root node drag
          copy: false,
          touch: "selected",
          open_timeout: 0,
          drag_selection: false,
          is_draggable: (nodes) => {
            return this.dndEnabled && nodes[0].parent !== "#";
          },
        },
        core: {
          data: {
            url: (node) => {
              // Get results
              var queryString =
                "?nodeLimit=" + (this.pager.getSkip() + this.pager.getLimit());

              return node.id === "#"
                ? window.location.pathname.match("^[^;]*")[0] +
                    this.pathToApi +
                    queryString
                : node.a_attr.href + this.pathToApi;
            },
            data: (node) => {
              return node.id === "#"
                ? { firstLoad: true }
                : {
                    firstLoad: false,
                    referenceCode: node.original.referenceCode,
                  };
            },

            dataFilter: (response) => {
              // Data from the initial request for hierarchy data contains
              // additional data relating to paging so we need to parse to
              // differentiate it.
              var data = JSON.parse(response);

              // Note root node's href and set number of available items to page through
              if (this.pager.rootId == "#") {
                this.pager.rootId = data.nodes[0].id;
                this.pager.setTotal(data.nodes[0].total);
              }

              // Allow for both styles of nodes
              if (typeof data.nodes === "undefined") {
                // Data is an array of jsTree node definitions
                return JSON.stringify(data);
              } else {
                // Data includes both nodes and the total number of available nodes
                return JSON.stringify(data.nodes);
              }
            },
          },

          check_callback: (
            operation,
            node,
            node_parent,
            node_position,
            more
          ) => {
            // Operations allowed:
            // - Before and after drag and drop between siblings
            // - Move core operations (node drop event)
            return (
              operation === "create_node" ||
              (operation === "move_node" &&
                (more.core ||
                  (more.dnd &&
                    node.parent === more.ref.parent &&
                    more.pos !== "i")))
            );
          },
        },
      };
      this.init(this.options);
    }

    init(options) {
      // Initialize jstree with options and listeners
      this.$fwTreeView
        .jstree(options)
        .bind("ready.jstree", this.readyListener)
        .bind("select_node.jstree", this.selectNodeListener)
        .bind("hover_node.jstree", this.hoverNodeListener)
        .bind("move_node.jstree", this.moveNodeListener);

      // Clicking "more" will add next page of results to tree
      this.$moreButton.on("click", () => {
        this.pager.next();
        this.pager.getAndAppendNodes(() => {
          // Queue is empty so update paging link
          this.pager.updateMoreLink(this.$moreButton, this.$resetButton);
        });
      });

      // Clicking reset link will reset paging and tree state
      $("#fullwidth-treeview-reset-button").on("click", () => {
        this.pager.reset(this.$moreButton, this.$resetButton);
      });

      this.$accordionButton.text(this.$treeViewConfig.data("closed-text"));

      // Set Treeview Accordion title.
      this.$accordionItem.on("shown.bs.collapse", (e) => {
        this.$accordionButton.text(this.$treeViewConfig.data("opened-text"));
      });
      this.$accordionItem.on("hidden.bs.collapse", (e) => {
        this.$accordionButton.text(this.$treeViewConfig.data("closed-text"));
      });

      // Set default Open/Close state for the treeview.
      bootstrap.Collapse.getOrCreateInstance(
        this.$accordionCollapsibleSection,
        {
          toggle: !this.treeViewCollapseEnabled,
        }
      );

      // This will scroll every time the accordion is opened.
      $(".full-treeview-section div.accordion-item").one(
        "shown.bs.collapse",
        (e) => {
          this.scrollToActive();
        }
      );

      // TODO restore window.history states
      $(window).on("popstate", function () {});
    }

    addButtonSection() {
      this.$buttonSection = $("<div>", {
        class: "d-flex flex-wrap gap-2 justify-content-end mb-3",
      });

      this.$buttonSection.append(this.$resetButton);
      this.$buttonSection.append(this.$moreButton);
    }

    addTreeviewToAccordion() {
      this.$accordionWrapper = $("<section>", {
        class: "accordion full-treeview-section mb-3",
      });
      this.$accordionItem = $("<div>", {
        class: "accordion-item",
      });
      var $accordionHeader = $("<h2>", {
        id: "heading-treeview",
        class: "accordion-header",
      });
      this.$accordionButton = $("<button>", {
        class: "accordion-button",
        type: "button",
        "data-bs-toggle": "collapse",
        "data-bs-target": "#collapse-treeview",
        "aria-expanded": "true",
        "aria-controls": "collapse-treeview",
      });
      this.$accordionCollapsibleSection = $("<div>", {
        id: "collapse-treeview",
        class: "accordion-collapse collapse",
        "aria-labelledby": "heading-treeview",
      });

      // Adjust bottom margins
      this.$fwTreeViewRow.css("margin-bottom", "0px");

      // Add wrapper to the DOM then hide the treeview and add it to the wrapper
      this.$mainHeader.after(this.$accordionWrapper);
      this.$fwTreeViewRow.hide();

      this.$accordionButton.appendTo($accordionHeader);
      $accordionHeader.appendTo(this.$accordionItem);
      this.$fwTreeViewRow.appendTo(this.$accordionCollapsibleSection);
      this.$accordionCollapsibleSection.appendTo(this.$accordionItem);
      this.$accordionItem.appendTo(this.$accordionWrapper);
      this.$fwTreeViewRow.show();
    }

    scrollToActive() {
      var $activeNode;

      $activeNode = $("li > a.jstree-clicked")[0];
      this.pager.updateMoreLink(this.$moreButton, this.$resetButton);

      // Override default scrolling
      if ($activeNode !== undefined) {
        $activeNode.scrollIntoView(false);
      }
    }

    showAlert(message, type) {
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
        $(".index #fullwidth-treeview-active").data("treeview-alert-close") +
        '"></button>';

      $alert.append(closeButton);

      $alert.prependTo($("body > #wrapper"));
      window.scrollTo({ top: 0 });

      return $alert;
    }

    deleteAlerts() {
      $("body > #wrapper > .alert").remove();
    }

    // Declare listeners
    // On ready: scroll to active node
    readyListener = () => {
      this.scrollToActive();
    };

    // On node selection: load the informationobject's page and insert the current page
    selectNodeListener = (e, data) => {
      // Open node if possible
      data.instance.open_node(data.node);

      // When an element is clicked in the tree ... fetch it up
      // window.location = window.location.origin + data.node.a_attr.href
      var url = data.node.a_attr.href;
      $.get(url, function (response) {
        response = $(response);

        // Insert new content into page
        $("#main-column h1")
          .first()
          .replaceWith($(response.find("#main-column h1").first()));

        // Add empty breadcrumb section if current page has none, but response does
        if (
          !$("#breadcrumb").length &&
          $(response.find("#breadcrumb").length)
        ) {
          var breadcrumbDestinationSelector = ".full-treeview-section";

          $(breadcrumbDestinationSelector).after(
            $("<nav>", { id: "breadcrumb" })
          );
        }
        $("#breadcrumb").replaceWith($(response.find("#breadcrumb")));

        // Replace description content
        $("#main-column .row").replaceWith(
          $(response.find("#main-column .row").first())
        );

        // If translation links exist in the response page, create element, if necessary,
        // and replace translation links in the current page with them
        if (
          response.find(".translation-links").length &&
          !$(".translation-links").length
        ) {
          $("#breadcrumb").after(
            $('<div class="btn-group translation-links"></div>')
          );
        }
        $(".translation-links").replaceWith(
          $(response.find(".translation-links"))
        );

        // Replace error message
        $("#main-column > .alert").remove();
        $("#breadcrumb").before(response.find("#main-column > .alert"));

        // Attach the Drupal Behaviour so blank.js does its thing
        Drupal.attachBehaviors(document);

        // Update clipboard buttons
        if (jQuery("#clipboard-menu").data("clipboard") !== undefined) {
          jQuery("#clipboard-menu").data("clipboard").updateAllButtons();
        }

        // Update the url, TODO save the state
        window.history.pushState(null, null, url);
      });
    };

    // On node move execute Ajax request to update the hierarchy in the
    // backend.
    moveNodeListener = (e, data) => {
      // Avoid request if new and old positions are the same,
      // this can't be avoided in the check_callback function
      // because we don't have both positions in there
      if (data.old_position === data.position) {
        return;
      }

      var moveResponse = JSON.parse(
        $.ajax({
          url:
            data.node.a_attr.href + "/informationobject/fullWidthTreeViewMove",
          type: "POST",
          async: false,
          data: {
            oldPosition: data.old_position,
            newPosition: data.position,
          },
        }).responseText
      );

      this.deleteAlerts();

      // Show alert with request result
      if (moveResponse.error) {
        this.showAlert(moveResponse.error, "alert-danger");

        // Reload treeview if failed
        data.instance.refresh();
      } else if ((moveResponse.success, "alert")) {
        this.showAlert(moveResponse.success, "alert-info");
      }
    };

    hoverNodeListener = (e, data) => {
      let parent = data.node.parent;
      let parentNode = this.$fwTreeView.jstree("get_json", parent);

      if (
        parent != "#" &&
        document.cookie.indexOf("atom_authenticated=1") != -1 &&
        !(parent in this.syncInitiated) &&
        "href" in parentNode.a_attr
      ) {
        this.syncInitiated[parent] = true;
        this.commandNodeAndChildren(this.$fwTreeView, parent, "disable_node");

        let url =
          parentNode.a_attr.href + "/informationobject/fullWidthTreeViewSync";

        $.get(url, (response) => {
          if (response["repair_successful"] === true) {
            // Refresh parent's child nodes if a repair was needed and was successful
            this.$fwTreeView.jstree("refresh_node", parent);
          } else if (response["repair_successful"] === false) {
            // Allow for syncing to be attempted again if a repair was needed, but failed
            delete this.syncInitiated[parent];
          }

          this.commandNodeAndChildren(this.$fwTreeView, parent, "enable_node");
        });
      }
    };

    commandNodeAndChildren = ($fwTreeView, id, command) => {
      let parent = $fwTreeView.jstree().get_node(id);
      $fwTreeView.jstree(command, id);
      parent.children.forEach((child) => $fwTreeView.jstree(command, child));
    };
  }

  $(() => {
    var $node = $(".index #fullwidth-treeview-active");
    if ($node.length) new FullWidthTreeView($node);
  });
})(jQuery);
