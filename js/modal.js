(($) => {
  "use strict";

  class TableModal {
    constructor(element) {
      // jQuery elements
      this.$element = $(element);
      this.$form = this.$element.closest("form");
      this.$loadError = this.$element.find(".load-error");
      this.$table = this.$element.find("table");
      this.$rowTemplate = this.$table.find(".row-template");
      this.$modal = this.$element.find(".modal");
      this.$validationError = this.$modal.find(".validation-error");
      this.$hiddenInputs = this.$modal.find("input[type=hidden]");

      // Other properties
      var inputId = this.$modal.find("input[id]:first").attr("id");
      this.prefix = inputId.substr(0, inputId.indexOf("_"));
      this.b5Modal = bootstrap.Modal.getOrCreateInstance(this.$modal);
      this.currentResource = this.$element.data("current-resource");
      this.currentResourceText = this.$element.data("current-resource-text");
      this.requiredFields = this.$element.data("required-fields")
        ? this.$element.data("required-fields").split(",")
        : [];
      this.deleteFieldName = this.$element.data("delete-field-name");
      this.checkedText = this.$element.data("checked-text");
      this.uncheckedText = this.$element.data("unchecked-text");
      this.iframeError = this.$element.data("iframe-error");
      this.lazyLoadUrl = this.$element.data("lazy-load-url");
      this.lazyLoadCount = 0;
      this.currentRowId = undefined;
      this.newRowsCounter = 0;
      this.rowsData = {};
      this.iframes = {};
      this.deleteRows = [];

      // Initial data load
      this.lazyLoad();

      // Listeners
      this.$element.on("click", ".add-row", this.addRow.bind(this));
      this.$element.on("click", ".edit-row", this.editRow.bind(this));
      this.$element.on("click", ".delete-row", this.deleteRow.bind(this));
      this.$element.on("click", ".modal-submit", this.submitModal.bind(this));
      this.$element.on("click", ".show-more", this.lazyLoad.bind(this));
      this.$modal.on("hidden.bs.modal", this.clearModal.bind(this));
      this.$form.on("submit", this.prepareAndSubmit.bind(this));

      // Extra listener for related donor autocomplete
      this.$modal.on(
        "change",
        'input[name="relatedDonor[resource]"]',
        this.updateContactInformation.bind(this)
      );

      // Extra listener for related actor relation type
      this.$modal.on(
        "change",
        'select[name="relatedAuthorityRecord[type]"]',
        this.toggleSubTypeInput.bind(this)
      );
    }

    lazyLoad() {
      if (!this.lazyLoadUrl) {
        this.$element.find(".show-more").remove();
        return;
      }

      // Skip already loaded data, limit is set in the URL
      $.get(this.lazyLoadUrl, { skip: this.lazyLoadCount })
        .done((res) => {
          $.each(res.data, (_, data) => {
            // Create and append new row with relation URL as id
            var $row = this.$rowTemplate
              .clone()
              .removeClass()
              .attr("id", data.url)
              .appendTo(this.$table.find("tbody"));
            // Transform data to match row fields and update row
            data.informationObject = data.title;
            this.updateRowContent($row, data);
            this.lazyLoadCount++;
          });
          // Remove show more button after all relations are loaded
          if (this.lazyLoadCount >= res.total) {
            this.$element.find(".show-more").remove();
          }
        })
        .fail(() => {
          this.$loadError.removeClass("d-none");
        });
    }

    updateRowContent($row, data) {
      $row.find("td").each((_, td) => {
        var $td = $(td);
        var fieldId = $td.data("field-id");
        if (!fieldId) return;

        // Remove prefix from field id
        if (this.prefix.length) {
          fieldId = fieldId.substr(this.prefix.length + 1, fieldId.length);
        }
        $td.text(data[fieldId]);
      });
    }

    addRow() {
      // Focus first input after modal show, using `one()` to remove
      // the listener after this load and avoid the focus on edit.
      this.$modal.one("shown.bs.modal", () =>
        this.$modal.find("input:focusable:first").trigger("focus")
      );
      this.b5Modal.show();
    }

    editRow(event) {
      var rowId = $(event.target).closest("tr").attr("id");
      if (!rowId) return;

      // Use existing relation data
      if (this.rowsData[rowId]) {
        this.loadModal(rowId);
        return;
      }

      // Fetch relation data
      $.get(rowId)
        .done((res) => {
          // Transform response data
          res = JSON.parse(res);
          res.resource = { uri: res.object, text: res.objectDisplay };
          res.subType = { uri: res.subType, text: res.subTypeDisplay };
          // If the current resource is the relation object,
          // use the subject and the converse sub type.
          if (this.currentResource === res.object) {
            res.resource = { uri: res.subject, text: res.subjectDisplay };
            res.subType = {
              uri: res.converseSubType,
              text: res.converseSubTypeDisplay,
            };
          }
          // Append current resource text to sub type display
          if (res.subType.text) {
            res.subType.text += " " + this.currentResourceText;
          }
          res.actor = { uri: res.actor, text: res.actorDisplay };
          res.place = { uri: res.place, text: res.placeDisplay };
          res.informationObject = {
            uri: res.informationObject,
            text: res.informationObjectDisplay,
          };
          // Remove no longer needed data
          [
            "object",
            "objectDisplay",
            "subject",
            "subjectDisplay",
            "actorDisplay",
            "placeDisplay",
            "subTypeDisplay",
            "converseSubType",
            "converseSubTypeDisplay",
            "informationObjectDisplay",
          ].forEach((key) => delete res[key]);
          this.rowsData[rowId] = res;
          this.loadModal(rowId);
        })
        .fail(() => {
          this.$loadError.removeClass("d-none");
        });
    }

    loadModal(rowId) {
      // Store row id to update data on modal submit
      this.currentRowId = rowId;
      if (this.rowsData[rowId]) {
        this.updateInputs(this.rowsData[rowId]);
      }
      this.b5Modal.show();
    }

    updateInputs(data) {
      $.each(data, (key, value) => {
        // Prepend prefix to data key to match field id
        var id = key;
        if (this.prefix.length) {
          id = this.prefix + "_" + key;
        }

        var $input = this.$modal.find("[id=" + id + "]");
        if (!$input.length) return;

        // Add source culture div for translations
        if (
          !value &&
          data["_sourceCulture"] &&
          data["_sourceCulture"]["fields"][key]
        ) {
          var $translation = $("<div>", { class: "default-translation" }).text(
            data["_sourceCulture"]["fields"][key]
          );
          if (data["_sourceCulture"]["direction"]) {
            $translation.attr("dir", data["_sourceCulture"]["direction"]);
          }
          $input.before($translation);
        }

        if (
          $input.attr("type") === "text" ||
          ["TEXTAREA", "SELECT"].includes($input.prop("tagName"))
        ) {
          // Use text and URI values to update autocomplete fields
          if (
            $input.hasClass("form-autocomplete") &&
            typeof value === "object" &&
            value
          ) {
            $input.val(value.text);
            $input.prev("input[type=hidden]").val(value.uri);
          } else if (value) {
            $input.val(value);
          }
        } else if ($input.attr("type") === "checkbox") {
          $input.prop("checked", value);
        }

        // Enable related actor sub type input if type has vale
        if (id === "relatedAuthorityRecord_type" && value) {
          this.$modal
            .find("#relatedAuthorityRecord_subType")
            .removeAttr("disabled");
        }
      });
    }

    deleteRow(event) {
      var $row = $(event.target).closest("tr");
      var rowId = $row.attr("id");
      // Store existing relations to delete
      if (!rowId.startsWith("new_relation_")) {
        this.deleteRows.push(rowId);
      }
      // Delete loaded data and iframe if they exist
      if (this.rowsData[rowId]) delete this.rowsData[rowId];
      if (this.iframes[rowId]) {
        $.each(this.iframes[rowId], (_, iframe) => iframe.remove());
        delete this.iframes[rowId];
      }
      // Remove row
      $row.hide(250, () => $row.remove());
    }

    clearModal() {
      // Clear all inputs
      this.clearInputs();

      // Show first tab panel when there are tabs
      var $tab = this.$modal.find(".nav-item:first button");
      if ($tab.length) bootstrap.Tab.getOrCreateInstance($tab).show();

      // Hide validation error alert and is-invalid classes
      this.$validationError.addClass("d-none");
      this.$modal
        .find(".is-invalid")
        .removeClass("is-invalid")
        .removeAttr("aria-invalid");

      // Remove translations
      this.$modal.find(".default-translation").remove();

      // Unset current row id
      this.currentRowId = undefined;
    }

    clearInputs(keep = []) {
      // Restore inputs, except those in keep
      this.$modal.find(":input").each((_, input) => {
        var $input = $(input);
        var inputId = $input.attr("id");
        if (keep.includes(inputId)) return;

        if (
          $input.attr("type") === "text" ||
          $input.prop("tagName") === "TEXTAREA"
        ) {
          $input.val("");
          // Clear previous hidden input in autocompletes
          if ($input.hasClass("form-autocomplete")) {
            $input.prev("input[type=hidden]").val("");
          }
        } else if ($input.prop("tagName") === "SELECT") {
          // Select first option
          $input.val($input.find("option:first").val());
        } else if ($input.attr("type") === "checkbox") {
          $input.prop("checked", false);
        }

        // Disable related actor sub type input
        if (inputId === "relatedAuthorityRecord_subType") {
          $input.attr("disabled", "disabled");
        }
      });
    }

    submitModal() {
      // Check required fields
      var avoidSubmit = false;
      $.each(this.requiredFields, (_, fieldId) => {
        var $input = this.$modal.find("#" + fieldId);
        if (!$input.val().length) {
          avoidSubmit = true;
          $input.addClass("is-invalid").attr("aria-invalid", true);
        }
      });

      // Show alert and avoid submit on validation error
      if (avoidSubmit) {
        this.$validationError.removeClass("d-none");
        return false;
      }

      // Create row or get existing one
      if (!this.currentRowId) {
        this.currentRowId = "new_relation_" + this.newRowsCounter++;
        var $row = this.$rowTemplate
          .clone()
          .removeClass()
          .attr("id", this.currentRowId)
          .appendTo(this.$table.find("tbody"));
      } else {
        var $row = this.$table.find('[id="' + this.currentRowId + '"]');
      }

      // Add/update row data and save display values for row content
      var data = {};
      var displayValues = {};
      this.$modal.find(":input").each((_, input) => {
        var $input = $(input);
        // Remove prefix from field id to match data keys
        var key = $input.attr("id");
        if (key && this.prefix.length) {
          key = key.substr(this.prefix.length + 1, key.length);
        }

        // Save autocomplete fields as objects with text and URI
        if ($input.hasClass("form-autocomplete")) {
          data[key] = {
            text: $input.val(),
            uri: $input.prev("input[type=hidden]").val(),
          };
          displayValues[key] = data[key]["text"];

          // Allow adding new values via iframe
          var $addInput = $input.siblings(".add");
          if ($addInput.length) {
            // Check existing iframe data
            this.iframes[this.currentRowId] =
              this.iframes[this.currentRowId] || {};
            if (this.iframes[this.currentRowId][key]) {
              if (data[key]["uri"].length || !data[key]["text"].length) {
                // Delete if we already have an URI or not value
                delete this.iframes[this.currentRowId][key];
              } else {
                // Update value
                this.iframes[this.currentRowId][key]["value"] =
                  data[key]["text"];
              }
            } else if (!data[key]["uri"].length && data[key]["text"].length) {
              // Create new iframe if there is value but not URI
              var addParts = $addInput.val().split(" ");
              var $iframe = $(
                '<iframe src="' + addParts[0] + '" class="d-none">'
              );
              // Add it to the body directly to trigger initial load
              $iframe.appendTo("body");
              // Save data for submit
              this.iframes[this.currentRowId][key] = {
                value: data[key]["text"],
                selector: addParts[1],
                $iframe: $iframe,
              };
            }
          }
        } else if (
          $input.attr("type") === "text" ||
          $input.prop("tagName") === "TEXTAREA"
        ) {
          data[key] = $input.val();
          displayValues[key] = data[key];
        } else if ($input.prop("tagName") === "SELECT") {
          data[key] = $input.val();
          displayValues[key] = $input.find("option:selected").text();
        } else if ($input.attr("type") === "checkbox") {
          data[key] = $input.is(":checked");
          displayValues[key] = data[key]
            ? this.checkedText
            : this.uncheckedText;
        }
      });

      // Update row data and content and hide modal
      this.rowsData[this.currentRowId] = data;
      this.updateRowContent($row, displayValues);
      this.b5Modal.hide();
    }

    prepareAndSubmit(event, prepared = false) {
      // Submit form if it's already prepared
      if (prepared) return;

      // Prevent form submit otherwise
      event.preventDefault();

      // Nothing else to do if the modal is open
      if (this.b5Modal._isShown) return;

      // Submit autocomplete iframes
      var iframeSubmits = [];
      $.each(this.iframes, (relationId, fieldData) => {
        $.each(fieldData, (key, data) => {
          // Create an iterable of promises that are resolved on iframe
          // load after form submission, or rejected on iframe error.
          iframeSubmits.push(
            new Promise((resolve, reject) => {
              data.$iframe.on("load", (event) => {
                // Load is triggered in case of submit failure instead of error,
                // check loaded URI against iframe source to know the result.
                var loadedUri = event.target.contentWindow.location.pathname;
                if (loadedUri === data.$iframe.attr("src")) {
                  // Reject with autocomplete text to show alert on error
                  reject(data.value);
                } else {
                  // Update relation data URI value with location pathname
                  this.rowsData[relationId][key]["uri"] = loadedUri;
                  resolve();
                }
              });
            })
          );

          // Add autocomplete value to iframe and submit form
          $(data.$iframe[0].contentWindow.document)
            .find(data.selector)
            .val(data.value)
            .closest("form")
            .trigger("submit");
        });
      });

      // Continue after all promises are resolved/rejected
      Promise.allSettled(iframeSubmits).then((results) => {
        // Check results and show alert if any iframe submit failed
        var $list = $("<ul>", { class: "mb-0 mt-2" });
        $.each(results, (_, result) => {
          if (result.status === "rejected") {
            $("<li>").text(result.reason).appendTo($list);
          }
        });
        if ($list.children().length) {
          $("<div>", { class: "alert alert-danger mt-3", role: "alert" })
            .append($("<p>").text(this.iframeError))
            .append($list)
            .insertBefore(".actions");
          return;
        }

        var counter = 0;
        var prefix = "dialog";
        if (this.prefix.length) {
          prefix = this.prefix + "s";
        }

        // Add rows data as hidden inputs to the main form
        $.each(this.rowsData, (relationId, data) => {
          // Add id of existing relations
          if (!relationId.startsWith("new_relation_")) {
            var name = prefix + "[" + counter + "][id]";
            this.$form.append(
              '<input type="hidden" name="' +
                name +
                '" value="' +
                relationId +
                '">'
            );
          }

          // Add hidden inputs
          this.$hiddenInputs.each((_, input) => {
            var $input = $(input);
            // Except those from autocomplete fields
            if (!$input.parent(".yui-ac").length) {
              // Clone after checking parent and before updating name
              $input = $input.clone();
              var name = $input.attr("name");
              name = name.replace(this.prefix, prefix + "[" + counter + "]");
              $input.attr("name", name);
              this.$form.append($input);
            }
          });

          // Add relation data
          $.each(data, (fieldId, value) => {
            var name = prefix + "[" + counter + "][" + fieldId + "]";
            // Use URI for autocomplete fields
            if (typeof value === "object" && value) {
              value = value.uri;
            }
            this.$form.append(
              '<input type="hidden" name="' + name + '" value="' + value + '">'
            );
          });

          counter++;
        });

        // Add hidden inputs to delete relations
        $.each(this.deleteRows, (index, rowId) => {
          // Special case for contact information URIs
          if (rowId.includes("/id/")) {
            rowId = rowId.split("/id/")[1];
          }
          this.$form.append(
            '<input type="hidden" name="' +
              this.deleteFieldName +
              "[" +
              this.prefix +
              index +
              ']" value="' +
              rowId +
              '"/>'
          );
        });

        // Remove modal to avoid default inputs submit
        this.$modal.remove();

        // Trigger final submit
        this.$form.trigger("submit", true);
      });
    }

    updateContactInformation(event) {
      // Fetch and update/clear primary contact information inputs
      var uri = $(event.target).val();
      if (uri) {
        $.get(uri + "/donor/primaryContact")
          .done((res) => this.updateInputs(res))
          .fail(() => this.clearInputs(["relatedDonor_resource"]));
      } else {
        this.clearInputs(["relatedDonor_resource"]);
      }
    }

    toggleSubTypeInput(event) {
      var type = $(event.target).val();
      var $subType = this.$modal.find("#relatedAuthorityRecord_subType");
      $subType.val("");
      $subType.prev("input[type=hidden]").val("");
      if (type) {
        $subType.removeAttr("disabled");
      } else {
        $subType.attr("disabled", "disabled");
      }
    }
  }

  $(() => $(".atom-table-modal").each((_, el) => new TableModal(el)));
})(jQuery);
