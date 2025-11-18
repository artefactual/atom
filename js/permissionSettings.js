(($) => {
  "use strict";

  $(() => {
    const $body = $("body.settings.permissions");
    if ($body.length) new PermissionSettings($body);
  });

  class PermissionSettings {
    constructor($body) {
      const $permissionsCollapse = $body.find("#permissions-collapse");
      const $copyrightCollapse = $body.find("#copyright-collapse");

      // Propagate visual hint from initial form state.
      this.$cells = $permissionsCollapse.find("input[type=checkbox]").parent();
      this.$cells.filter(":has(input:checked)").addClass("checked");

      $permissionsCollapse
        .on("change", "td", this.changeCellState.bind(this))
        .on("click", ".all", this.enableAll.bind(this))
        .on("click", ".none", this.disableAll.bind(this))
        .on("click", "td", this.clickCell.bind(this))
        .on("click", "button", this.toggleColumn.bind(this));

      $copyrightCollapse.on(
        "click",
        "input[name=preview]",
        this.previewStatement.bind(this)
      );
    }

    // Toggle state of one or more permissions.
    static togglePermission($nodes, state) {
      $nodes.each((index, node) => {
        // Find the chekbox and its cell container.
        let $cell, $input;
        switch (node.tagName) {
          case "INPUT":
            $input = $(node);
            $cell = $input.parent();
            break;
          case "TD":
            $cell = $(node);
            $input = $cell.find("input");
            break;
          default:
            return false;
        }

        // When state is not provided, toggle it.
        if (typeof state === "undefined") {
          state = !$cell.hasClass("checked");
        }

        $cell.toggleClass("checked", state);
        $input.prop("checked", state);
      });
    }

    // Enable all permissions.
    enableAll(event) {
      event.preventDefault();
      PermissionSettings.togglePermission(this.$cells, true);
    }

    // Disable all permissions.
    disableAll(event) {
      event.preventDefault();
      PermissionSettings.togglePermission(this.$cells, false);
    }

    // When the event is fired from the checkbox.
    changeCellState(event) {
      const $input = $(event.target);
      PermissionSettings.togglePermission($input, $input.prop("checked"));
    }

    // When the state change is triggered by clicking the cell.
    clickCell(event) {
      if (event.target.tagName === "TD")
        PermissionSettings.togglePermission($(event.target));
    }

    // Update all permissions on a given column.
    toggleColumn(event) {
      event.preventDefault();
      const $btn = $(event.target);

      // Find all cells in this column.
      const $cells = $btn
        .closest("table")
        .find("tr td:nth-child(" + ($btn.parent().index() + 2) + ")");

      // Enable unless it's already enabled, then disable.
      let state = false;
      $cells.find("input[type=checkbox]").each((index, element) => {
        if (!element.checked) {
          state = true;
          return false;
        }
      });

      PermissionSettings.togglePermission($cells, state);
    }

    // Open the preview statement page in a new window or tab.
    previewStatement(event) {
      event.preventDefault();

      const $form = $(event.target).closest("form").attr("target", "_blank");
      const $input = $("<input/>", {
        type: "hidden",
        name: "preview",
        value: "true",
      }).appendTo($form);

      $form.trigger("submit").removeAttr("target");
      $input.remove();
    }
  }
})(jQuery);
