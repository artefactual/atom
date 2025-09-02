// $Id: l10n_client.js 6570 2010-04-26 21:31:07Z jablko $
(function ($) {
  // Set "selected" string to unselected, i.e. -1
  $.extend(Drupal, {
    l10nSelected: -1,
    l10nSourceInputs: [],
    l10nTargetInputs: [],
    l10nSourceMessages: [],
    l10nTargetMessages: [],
  });

  // Get messages from DOM
  var $translate = $("#translate-plugin");
  if ($translate.length) {
    Drupal.l10nSourceMessages = $translate.data("l10n-source-messages");
    Drupal.l10nTargetMessages = $translate.data("l10n-target-messages");
  }

  /**
   * Attaches the localization editor behaviour to all required fields.
   */
  Drupal.behaviors.l10nEditor = {
    attach: function (context) {
      $("#l10n-client-hide").click(function () {
        $(
          "#l10n-client-string-select, #l10n-client-string-editor, #l10n-client .labels .lbl"
        ).hide();
        $("#l10n-client").height("2em");
        $("#l10n-client-hide").hide();
        $("#l10n-client-show").show();
        $("body").css("border-bottom", "0px");
      });

      $("#l10n-client-show").click(function () {
        $(
          "#l10n-client-string-select, #l10n-client-string-editor, #l10n-client .labels .lbl"
        ).show();
        $("#l10n-client").height("22em");
        $("#l10n-client-hide").show();
        $("#l10n-client-show").hide();
        $("body").css("border-bottom", "22em solid #fff");
      });

      // Add class to indicate selected string in list widget.
      $("#l10n-client-string-select li").click(function () {
        var index = $("#l10n-client-string-select li").index(this);
        var listItem = $(this);

        $("#l10n-client-string-select li").removeClass("active");
        listItem.addClass("active");

        $("#l10n-client-string-editor textarea").hide();

        if (Drupal.l10nSourceInputs[index] == null) {
          Drupal.l10nSourceInputs[index] = $(document.createElement("textarea"))
            .attr("name", "source[]")
            .attr("rows", 6)
            .attr("readonly", "readonly")
            .appendTo("#l10n-client-string-editor .source");
          Drupal.l10nSourceInputs[index].val(Drupal.l10nSourceMessages[index]);
        }

        if (Drupal.l10nTargetInputs[index] == null) {
          Drupal.l10nTargetInputs[index] = $(document.createElement("textarea"))
            .attr("name", "target[]")
            .attr("rows", 6)
            .appendTo("#l10n-client-string-editor .translation");
          Drupal.l10nTargetInputs[index].val(Drupal.l10nTargetMessages[index]);
          Drupal.l10nTargetInputs[index].blur(function () {
            if ($(this).val() == "") {
              listItem.removeClass("translated").addClass("untranslated");
              return;
            }

            listItem.removeClass("untranslated").addClass("translated");
          });
        }

        Drupal.l10nSourceInputs[index].show();
        Drupal.l10nTargetInputs[index].show();
        Drupal.l10nTargetInputs[index].select();

        Drupal.l10nSelected = index;
      });

      // Mark all strings depending on whether they are translated or not.
      for (var i in Drupal.l10nTargetMessages) {
        $($("#l10n-client-string-select li")[i]).addClass(
          Drupal.l10nTargetMessages[i] == "" ? "untranslated" : "translated"
        );
      }

      // Copy source text to translation field on button click.
      $("#l10n-client-form #edit-copy").click(function () {
        $("#l10n-client-form #edit-target").val(
          $("#l10n-client-string-editor .source-text").text()
        );
      });

      // Clear translation field on button click.
      $("#l10n-client-form #edit-clear").click(function () {
        $("#l10n-client-form #edit-target").val("");
      });
    },
  };
})(jQuery);
