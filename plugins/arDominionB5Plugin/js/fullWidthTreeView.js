(function ($) {
  "use strict";

  $(function () {
    var $node = $(".index #fullwidth-treeview-active");

    if ($node.length) {
      loadTreeView();
    }
  });

  function addTreeviewToAccordion($mainHeader, $fwTreeViewRow) {
    var $accordionWrapper = $("<section>", {
      class: "accordion full-treeview-section",
    });
    var $accordionItem = $("<div>", {
      class: "accordion-item",
    });
    var $accordionHeader = $("<h2>", {
      id: "heading-treeview",
      class: "accordion-header",
    });
    var $accordionButton = $("<button>", {
      class: "accordion-button",
      type: "button",
      "data-bs-toggle": "collapse",
      "data-bs-target": "#collapse-treeview",
      "aria-expanded": "true",
      "aria-controls": "collapse-treeview",
    });
    var $accordionCollapsibleSection = $("<div>", {
      id: "collapse-treeview",
      class: "accordion-collapse collapse",
      "aria-labelledby": "heading-treeview",
    });

    // Adjust bottom margins
    var bottomMargin = $fwTreeViewRow.css("margin-bottom");
    $fwTreeViewRow.css("margin-bottom", "0px");
    $accordionWrapper.css("margin-bottom", bottomMargin);

    // Add wrapper to the DOM then hide the treeview and add it to the wrapper
    $mainHeader.after($accordionWrapper);
    $fwTreeViewRow.hide();

    $accordionButton.appendTo($accordionHeader);
    $accordionHeader.appendTo($accordionItem);
    $fwTreeViewRow.appendTo($accordionCollapsibleSection);
    $accordionCollapsibleSection.appendTo($accordionItem);
    $accordionItem.appendTo($accordionWrapper);
    $fwTreeViewRow.show();
  }

  function loadTreeView() {
    var $treeViewConfig = $("#fullwidth-treeview-configuration");
    var treeViewCollapseEnabled =
      $treeViewConfig.data("collapse-enabled") == "yes";
    var collectionUrl = $treeViewConfig.data("collection-url");
    var itemsPerPage = $treeViewConfig.data("items-per-page");
    var pathToApi = "/informationobject/fullWidthTreeView";
    var $fwTreeView = $('<div id="fullwidth-treeview"></div>');
    var $fwTreeViewRow = $('<div id="fullwidth-treeview-row"></div>');
    var $mainHeader = $("#main-column h1").first();
    var $moreButton = $("#fullwidth-treeview-more-button");
    var $resetButton = $("#fullwidth-treeview-reset-button");
    var pager = new Qubit.TreeviewPager(
      itemsPerPage,
      $fwTreeView,
      collectionUrl + pathToApi
    );

    // Add tree-view divs after main header, animate and allow resize
    $mainHeader.after(
      $fwTreeViewRow
        .append($fwTreeView)
        .animate({ height: "200px" }, 500)
        .resizable({ handles: "s" })
    );

    $mainHeader.before($resetButton);
    $mainHeader.before($moreButton);

    addTreeviewToAccordion($mainHeader, $fwTreeViewRow);

    // Declare jsTree options
    var options = {
      plugins: ["types", "dnd"],
      types: Qubit.treeviewTypes,
      dnd: {
        // Drag and drop configuration, disable:
        // - Node copy on drag
        // - Load nodes on hover while dragging
        // - Multiple node drag
        // - Root node drag
        copy: false,
        open_timeout: 0,
        drag_selection: false,
        is_draggable: function (nodes) {
          return nodes[0].parent !== "#";
        },
      },
      core: {
        data: {
          url: function (node) {
            // Get results
            var queryString =
              "?nodeLimit=" + (pager.getSkip() + pager.getLimit());

            return node.id === "#"
              ? window.location.pathname.match("^[^;]*")[0] +
                  pathToApi +
                  queryString
              : node.a_attr.href + pathToApi;
          },
          data: function (node) {
            return node.id === "#"
              ? { firstLoad: true }
              : {
                  firstLoad: false,
                  referenceCode: node.original.referenceCode,
                };
          },

          dataFilter: function (response) {
            // Data from the initial request for hierarchy data contains
            // additional data relating to paging so we need to parse to
            // differentiate it.
            var data = JSON.parse(response);

            // Note root node's href and set number of available items to page through
            if (pager.rootId == "#") {
              pager.rootId = data.nodes[0].id;
              pager.setTotal(data.nodes[0].total);
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
        check_callback: function (
          operation,
          node,
          node_parent,
          node_position,
          more
        ) {
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

    function scrollToActive() {
      var $activeNode;

      $activeNode = $("li > a.jstree-clicked")[0];
      pager.updateMoreLink($moreButton, $resetButton);

      // Override default scrolling
      if ($activeNode !== undefined) {
        $activeNode.scrollIntoView(false);
      }
    }

    var showAlert = function (message, type) {
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
    };

    var deleteAlerts = function () {
      $("body > #wrapper > .alert").remove();
    };

    // Declare listeners
    // On ready: scroll to active node
    var readyListener = function () {
      scrollToActive();
    };

    // On node selection: load the informationobject's page and insert the current page
    var selectNodeListener = function (e, data) {
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
    var moveNodeListener = function (e, data) {
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

      deleteAlerts();

      // Show alert with request result
      if (moveResponse.error) {
        showAlert(moveResponse.error, "alert-danger");

        // Reload treeview if failed
        data.instance.refresh();
      } else if ((moveResponse.success, "alert")) {
        showAlert(moveResponse.success, "alert-info");
      }
    };

    // Initialize jstree with options and listeners
    $fwTreeView
      .jstree(options)
      .bind("ready.jstree", readyListener)
      .bind("select_node.jstree", selectNodeListener)
      .bind("move_node.jstree", moveNodeListener);

    // Clicking "more" will add next page of results to tree
    $moreButton.on("click", function () {
      pager.next();
      pager.getAndAppendNodes(function () {
        // Queue is empty so update paging link
        pager.updateMoreLink($moreButton, $resetButton);
      });
    });

    // Clicking reset link will reset paging and tree state
    $("#fullwidth-treeview-reset-button").on("click", function () {
      pager.reset($moreButton, $resetButton);
    });

    // This will scroll every time the accordion is opened.
    $(".full-treeview-section div.accordion-item").one("shown.bs.collapse", (e) => {
      scrollToActive();
    });

    // TODO restore window.history states
    $(window).on("popstate", function () {});
  }
})(jQuery);
