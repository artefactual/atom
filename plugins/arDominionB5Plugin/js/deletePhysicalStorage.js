(($) => {
  "use strict";

  $(() => {
    $(".delete-physical-storage").on("click", function () {
      var $this = $(this);
      $this.closest("form").append(
        $("<input>", {
          type: "hidden",
          name: "delete_relations[]",
          value: $this.attr("id"),
        })
      );
      var $row = $this.closest("tr");
      $row.hide(250, () => $row.remove());
    });
  });
})(jQuery);
