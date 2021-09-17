(($) => {
  "use strict";

  $(() => new Rights($("#rights-form")));

  class Rights {
    constructor($element) {
      if (!$element.length) return;

      // Rights basis
      this.$basisSelect = $element.find("#right_basis");
      this.$basisFields = $element.find("[id$=-basis-fields]");

      this.toggleBasis();
      this.$basisSelect.on("change", this.toggleBasis.bind(this));

      // Act / Granted rights
      this.$addButton = $element.find("#act-rights-add");
      this.$addContent = $element.find("#act-rights-content-add");
      this.newText = this.$addButton.data("act-rights-new-text");
      this.counter = 0;

      this.$addButton.on("show.bs.tab", this.addAct.bind(this));
      $element.on("click", ".act-rights-delete", this.deleteAct.bind(this));
    }

    toggleBasis() {
      // Show/hide fields based on basis select value
      var value = this.$basisSelect.val().match("[^/]*$")[0];
      this.$basisFields.each((_, fields) => {
        var $fields = $(fields);
        $fields.toggle($fields.attr("id").startsWith(value));
      });
    }

    addAct(event, removeElements = []) {
      // Do not show the add tab
      event.preventDefault();

      // Duplicate add button and panel
      var $newButton = this.$addButton.clone();
      var $newContent = this.$addContent.clone();

      // Generate new act rights ids
      var buttonId = "act-rights-new-" + this.counter;
      var contentId = "act-rights-content-new-" + this.counter;

      // Create a li to wrap the new button, update button
      // and insert them in the list before the add button.
      $("<li>", { class: "nav-item", role: "presentation" })
        .append(
          $newButton
            .attr("id", buttonId)
            .attr("data-bs-target", "#" + contentId)
            .attr("aria-controls", contentId)
            .html(this.newText)
        )
        .insertBefore(this.$addButton.parent());

      // Update content attributes and insert it before the add content
      $newContent
        .attr("id", contentId)
        .attr("aria-labelledby", buttonId)
        .insertBefore(this.$addContent);

      // Create and show new tab
      $newButton.one("shown.bs.tab", () => {
        // Remove previous tab if we come from deleteAct
        removeElements.forEach(($element) => $element.remove());
        // Update inputs and focus first one
        this.updateInputs();
        $newContent.find(":input:focusable:first").trigger("focus");
      });
      new bootstrap.Tab($newButton).show();

      this.counter++;
    }

    deleteAct(event) {
      var $content = $(event.target.closest(".tab-pane[id^=act-rights]"));
      var $button = $("#" + $content.attr("aria-labelledby"));
      var $nextButton = $button.parent().next().children("button");

      // Remove always the button parent li
      var removeElements = [$button.parent()];
      if ($content.find("input[id$=id]").val() === "0") {
        // If it's a new act right, delete the content too
        removeElements.push($content);
      } else {
        // Otherwise, mark it for deletion
        $content.find("input[id$=delete]").val("true");
      }

      // If the next button is the add new, try to get the previous one
      if ($nextButton.attr("id") == this.$addButton.attr("id")) {
        var $prevButton = $button.parent().prev().children("button");
        // If there is none, add a new tab and remove this one
        if (!$prevButton.length) {
          // The removal needs to happen after the new tab is shown and
          // we can't listen to that event like we do below because it's
          // prevented when triggered over the add button. Therefore, we
          // pass the elements to remove to do it there.
          this.addAct(event, removeElements);
          return;
        }
        // Otherwhise, use the previous button tab
        $nextButton = $prevButton;
      }

      // Remove this tab after showing the new one and update inputs
      $nextButton.one("shown.bs.tab", () => {
        removeElements.forEach(($element) => $element.remove());
        this.updateInputs();
      });
      bootstrap.Tab.getOrCreateInstance($nextButton).show();
    }

    updateInputs() {
      $(".tab-pane[id^=act-rights]").each((index, content) => {
        var $content = $(content);
        if ($content.attr("id") == "act-rights-content-add") return;

        // Update inputs id and name attributes
        $content.find(":input[id]").each((_, input) => {
          var $input = $(input);
          $input.attr(
            "id",
            $input
              .attr("id")
              .replace(/_\d+_/, "_" + index + "_")
              .replace("_blank_", "_grantedRights_" + index + "_")
          );
          $input.attr(
            "name",
            $input
              .attr("name")
              .replace(/\[\d+\]/, "[" + index + "]")
              .replace("[blank]", "[grantedRights][" + index + "]")
          );
        });

        // Update labels for attribute
        $content.find("label[for]").each((_, label) => {
          var $label = $(label);
          $label.attr(
            "for",
            $label
              .attr("for")
              .replace(/_\d+_/, "_" + index + "_")
              .replace("_blank_", "_grantedRights_" + index + "_")
          );
        });
      });
    }
  }
})(jQuery);
