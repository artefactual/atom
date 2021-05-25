"use strict";

(function($, Qubit, Drupal) {
  // Set up dialog
  var dialog;

  Drupal.behaviors.event = {
    attach: function(context) {
      // Add special rendering rules
      var handleFieldRender = function(fname) {
        if (
          -1 !== fname.indexOf("date") &&
          0 == this.getField("date").value.length &&
          (0 < this.getField("startDate").value.length ||
            0 < this.getField("endDate").value.length)
        ) {
          return (
            this.getField("startDate").value +
            " - " +
            this.getField("endDate").value
          );
        }

        return this.renderField(fname);
      };

      // Add validator to make sure that an information object is selected
      var validator = function(data) {
        var informationObject = data["editEvent[informationObject]"];

        if (!informationObject.length) {
          // Display error message
          jQuery('#resourceRelationError').css('display', 'block');

          return false;
        } else {
          // Hide error message until required again
          jQuery('#resourceRelationError').css('display', 'none');
        }
      }

      // Hide error on cancel
      var afterCancelLogic = function () {
          jQuery('#resourceRelationError').css('display', 'none');
      }

      // Define dialog
      dialog = new QubitDialog("resourceRelation", {
        displayTable: "relatedEvents",
        handleFieldRender: handleFieldRender,
        newRowTemplate: $("#dialogNewRowTemplate").html(),
        validator: validator,
        afterCancelLogic: afterCancelLogic
      });
    }
  };

  // Set up relation display/paging
  $(function() {
    var url = "/sfIsaarPlugin/actorEvents";

    // Modifying the location causes issues when using Drupal behaviors
    var pager = new Qubit.Pager(20, { disableLocationHashStorage: true });

    var $tbody = $("#actorEvents");
    var $nextButton = $("#actorEventsNextButton");
    var slug = $nextButton.data("slug");

    // "More" button will add new rows
    $nextButton.click(function() {
      pager.next();
      draw();
    });

    function draw() {
      var urlWithArgs = url + "?slug=" + slug + "&skip={skip}&limit={limit}";

      // Do nothing if no slug provided
      if (slug == "") {
        return;
      }

      $.ajax({
        url: pager.replaceUrlTags(urlWithArgs),
        success: function(data) {

          // Add rows using data
          for (var i = 0; i < data.data.length; i++) {
            addRow(data.data[i]);
          }

          // Add control elements to rows (edit/delete)
          Drupal.behaviors.multiDelete.attach();
          addEditButtonToRows();

          // Update pager and determine whether "more" button is still valid
          pager.setTotal(data.total);

          // Remove "More" button if no more rows are available
          if (!pager.getRemaining()) {
            $nextButton.remove();
          } else {
            $nextButton.removeClass("invisible");
          }
        }
      });
    }

    function addRow(actorEvent) {
      var $row = $("<tr/>").attr("id", actorEvent["url"]);

      $row.append($("<td/>").text(actorEvent["title"]));
      $row.append($("<td/>").text(actorEvent["type"]));
      $row.append($("<td/>").text(actorEvent["date"]));

      var $controlColumn = $("<td/>").attr("style", "text-align: right");
      $controlColumn.append(
        $("<input/>")
          .attr("class", "multiDelete")
          .attr("name", "deleteEvents[]")
          .attr("type", "checkbox")
          .attr("value", actorEvent["url"])
      );

      $row.append($controlColumn);

      $tbody.append($row);
    }

    function addEditButtonToRows() {
      $("#relatedEvents tr[id]").each(function(index, value) {
        if (!$(this).data("editAdded")) {
          $(this)
            .click(function() {
              dialog.open(this.id);
            })
            .find("td:last")
            .prepend($("#editButtonTemplate").html() + " ");

          $(this).data("editAdded", true);
        }
      });
    }

    draw();
  });
})(jQuery, Qubit, Drupal);
