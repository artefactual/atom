(function ($) {
    "use strict";

    const $addInfoObjectsToClipboard = $("#add-info-objects-to-clipboard");

    if ($addInfoObjectsToClipboard.length) {
        $addInfoObjectsToClipboard.on("click", function () {
            let slugs = $(this).data("slugs");

            if (!Array.isArray(slugs) || !window.atomClipboard) {
                return;
            }

            atomClipboard.bulkAddItems(
                slugs,
                "informationObject",
                $(this).data("single-added-message"),
                $(this).data("plural-added-message"),
                $(this).data("already-added-message"),
            );
        });
    }

    const $addAccessionsToClipboard = $("#add-accessions-to-clipboard");

    if ($addAccessionsToClipboard.length) {
        $addAccessionsToClipboard.on("click", function () {
            let slugs = $(this).data("slugs");

            if (!Array.isArray(slugs) || !window.atomClipboard) {
                return;
            }

            atomClipboard.bulkAddItems(
                slugs,
                "accession",
                $(this).data("single-added-message"),
                $(this).data("plural-added-message"),
                $(this).data("already-added-message"),
            );
        });
    }
})(jQuery);
