(($) => {
  "use strict";

  $(() => {
    var $treeviewAccordionItem = $(".full-treeview-section div.accordion-item");

    if ($treeviewAccordionItem.length) {
      var $accordionButton = $treeviewAccordionItem.find(
        "button.accordion-button"
      );
      var $accordionCollapsibleSection = $("div#collapse-treeview.collapse");

      var $treeViewConfig = $("#fullwidth-treeview-configuration");
      var treeViewCollapseEnabled =
        $treeViewConfig.data("collapse-enabled") == "yes";

      $accordionButton.text($treeViewConfig.data("closed-text"));

      // Set Treeview Accordion title.
      $treeviewAccordionItem.on("show.bs.collapse", (e) => {
        $accordionButton.text($treeViewConfig.data("opened-text"));
      });
      $treeviewAccordionItem.on("hide.bs.collapse", (e) => {
        $accordionButton.text($treeViewConfig.data("closed-text"));
      });

      // Set default Open/Close state for the treeview.
      bootstrap.Collapse.getOrCreateInstance($accordionCollapsibleSection, {
        toggle: treeViewCollapseEnabled,
      });
    }
  });
})(jQuery);
