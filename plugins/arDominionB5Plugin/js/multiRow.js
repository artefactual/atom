(($) => {
  "use strict";

  $(() => {
    $("table.multi-row").each((_, table) => {
      var $table = $(table);

      $table.on("click", ".multi-row-add", function () {
        var $lastRow = $table.find("> tbody > tr:last");
        var $newRow = $lastRow.clone().hide();

        // Get the last row number (e.g.: foo[0][bar])
        var lastRowNumber = parseInt(
          $lastRow
            .find("select, input, textarea")
            .first()
            .attr("name")
            .match(/\d+/)
            .shift()
        );

        // Replace YUI div with form-autocomplete elements
        $newRow.find(".yui-ac").each(function () {
          var $this = $(this);
          var name = $this.children("input[name]:first").attr("name");
          var id = $this.children("input[id]:first").attr("id");
          var inputAdd = $this.children("input[class=add]")[0];
          var inputList = $this.children("input[class=list]")[0];
          $this.replaceWith(
            $("<div>")
              .append(
                $("<select>", {
                  id: id,
                  name: name,
                  class: "form-autocomplete form-control",
                })
              )
              .append(inputAdd)
              .append(inputList)
          );
        });

        // Iterate over each input, select and textarea elements
        $newRow.find("input, select, textarea").each(function (i) {
          var $this = $(this);

          // Input values are removed, except those hidden
          // that are not related to start/end dates.
          if (
            $this.is("input, textarea") &&
            ($this.attr("type") != "hidden" ||
              ($this.attr("id") && $this.attr("id").endsWith("Date")))
          ) {
            $this.val("");
          } else if ($this.is("select")) {
            // Select index is preserved
            var oldSelect = $lastRow.find("input, select, textarea").eq(i);
            var selectedIndex = oldSelect[0].selectedIndex;

            $this[0].selectedIndex = selectedIndex;
          }

          // Increment row number
          if ($this.attr("name")) {
            var newName = $this
              .attr("name")
              .replace(/\[\d+\]/, "[" + (lastRowNumber + 1) + "]");
            $this.attr("name", newName);
          }
          if ($this.attr("id")) {
            var newId = $this
              .attr("id")
              .replace(/\_\d+\_/, "_" + (lastRowNumber + 1) + "_");
            $this.attr("id", newId);
          }
        });

        // Append, attach autocomplete, show and trigger focus on first input
        $table.children("tbody").append($newRow);
        Drupal.behaviors.autocomplete.attach();
        $newRow.show(250, () =>
          $newRow.find(":input:focusable").first().trigger("focus")
        );
      });

      // If user press enter, add new row
      $table.on("keydown", "input, select", function (event) {
        if (event.key == "Enter" && 0 == $table.find(":animated").length) {
          $table.find(".multi-row-add").trigger("click");
          return false;
        }
      });

      $table.on("click", ".multi-row-delete", function () {
        var $this = $(this);
        var $rows = $table.find("tbody tr");
        var $objectRows = $table.find('tr[class^="related_obj_"]');
        var $row = $this.closest("tr");

        // Deleting element sometimes causes focusout not to fire in Firefox
        $this.trigger("focusout");

        // Only remove row if it isn't the last one without a related object.
        // Adding a new row based on a related object row may creates id
        // duplication or not form fields in the new row.
        if (
          1 < $rows.length - $objectRows.length ||
          ($row.attr("class") &&
            $row.attr("class").indexOf("related_obj_") >= 0)
        ) {
          var rowNumber = parseInt(
            $row
              .find("select, input, textarea")
              .first()
              .attr("name")
              .match(/\d+/)
              .shift()
          );

          rowNumber--;

          $row.nextAll().each(function () {
            rowNumber++;

            $(this)
              .find("input, select, textarea")
              .each(function () {
                var $this = $(this);
                if ($this.attr("name")) {
                  var newName = $this
                    .attr("name")
                    .replace(/\[\d+\]/, "[" + rowNumber + "]");
                  $this.attr("name", newName);
                }
                if ($this.attr("id")) {
                  var newId = $this
                    .attr("id")
                    .replace(/\_\d+\_/, "_" + rowNumber + "_");
                  $this.attr("id", newId);
                }
              });
          });

          $row.hide(250, () => $row.remove());
        } else {
          $row.find("input, select, textarea").each(function () {
            var $this = $(this);

            // Input values are removed except hidden ones
            if ($this.is("input, textarea") && $this.attr("type") != "hidden") {
              $this.val("");
            } else if ($this.is("select")) {
              $this[0].selectedIndex = 0;
            }
          });
        }
      });
    });
  });
})(jQuery);
