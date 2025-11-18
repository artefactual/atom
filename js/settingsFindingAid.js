(($) => {
  "use strict";

  $(() => {
    let $faControls = $("#finding-aid-collapse > .accordion-body > div").slice(
      1,
      4
    );

    function isEnabled() {
      return $("#finding_aid_finding_aids_enabled_1").is(":checked");
    }

    function initialize() {
      if (!isEnabled()) {
        $faControls.hide();
      }
    }

    function toggle() {
      if (isEnabled()) {
        $faControls.show();
      } else {
        $faControls.hide();
      }
    }

    // Main
    initialize();
    $("#finding_aid_finding_aids_enabled_0").on("change", toggle);
    $("#finding_aid_finding_aids_enabled_1").on("change", toggle);
  });
})(jQuery);
