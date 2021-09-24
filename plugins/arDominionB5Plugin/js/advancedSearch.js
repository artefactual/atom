(($) => {
  "use strict";

  $(() => $(".adv-search").each((_, el) => new AdvancedSearch(el)));

  class AdvancedSearch {
    constructor(element) {
      this.$element = $(element);
      this.$form = this.$element.find('form[name="advanced-search-form"]');
      this.$reposFacet = $("#heading-repos").closest(".accordion");
      this.$reposFilter = this.$element.find('select[name="repos"]');
      this.$collectionFilter = this.$element.find('input[name="collection"]');

      this.init();
      this.listen();
    }

    init() {
      // Hide last criteria if more than once.
      if (1 < this.$form.find(".criterion").length) {
        this.$form.find(".criterion:last").remove();
      }

      this.checkReposFilter();
    }

    listen() {
      this.$form
        .on(
          "click",
          ".add-new-criteria .dropdown-item",
          this.addCriterion.bind(this)
        )
        .on("click", "input.reset", this.reset.bind(this))
        .on("click", "a.delete-criterion", this.deleteCriterion.bind(this))
        .on("submit", this.submit.bind(this));

      this.$collectionFilter.on("change", this.checkReposFilter.bind(this));
    }

    checkReposFilter(event) {
      // Disable repository filter and facet if top-level description selected.
      if (
        typeof this.$collectionFilter !== "undefined" &&
        this.$reposFilter.length &&
        this.$collectionFilter.val() != ""
      ) {
        this.$reposFilter.attr("disabled", "disabled");
        this.$reposFilter.val("");
        if (this.$reposFacet.length) {
          this.$reposFacet.hide();
        }
      } else if (
        this.$reposFilter.length &&
        this.$collectionFilter.val() == ""
      ) {
        this.$reposFilter.removeAttr("disabled");
        if (this.$reposFacet.length) {
          this.$reposFacet.show();
        }
      }
    }

    submit(event) {
      // Disable empty fields and first operator in criteria.
      this.$form.find(':input[value=""]').attr("disabled", "disabled");
      this.$form.find('select[name="so0"]').attr("disabled", "disabled");
    }

    reset(event) {
      window.location.replace(
        this.$form.attr("action") + "?showAdvanced=1&topLod=0"
      );
    }

    addCriterion(event) {
      event.preventDefault();

      this.cloneLastCriterion()
        .insertAfter(this.$form.find(".criterion:last"))
        .show()
        .find(".adv-search-boolean select")
        .val(event.target.id.replace("add-criterion-", ""))
        .end()
        .find("input")
        .first()
        .trigger("focus");
    }

    cloneLastCriterion() {
      var $clone = this.$form.find(".criterion:last").clone();

      var nextNumber =
        parseInt($clone.find("input:first").attr("name").match(/\d+/).shift()) +
        1;

      $clone.find("input, select").each(function (index, element) {
        var name = this.getAttribute("name").replace(/[\d+]/, nextNumber);
        this.setAttribute("name", name);
      });

      AdvancedSearch.clearFormFields($clone);

      return $clone;
    }

    deleteCriterion(event) {
      event.preventDefault();

      var $criterion = $(event.target.closest(".criterion"));
      var targetNumber = parseInt(
        $criterion.find("input:first").attr("name").match(/\d+/).shift()
      );

      // First criterion without siblings, just clear that criterion.
      if (targetNumber == 0 && this.$form.find(".criterion").length == 1) {
        AdvancedSearch.clearFormFields($criterion);
        return;
      }

      // Otherwise update next siblings input and select names.
      $criterion.nextAll(".criterion").each(function () {
        var $this = $(this);
        var number = parseInt(
          $this.find("input:first").attr("name").match(/\d+/).shift()
        );
        $this.find("input, select").each(function (index, element) {
          var name = this.getAttribute("name").replace(/[\d+]/, number - 1);
          this.setAttribute("name", name);
        });
      });

      // Then delete criterion.
      $criterion.remove();
    }

    static clearFormFields($element) {
      $element.find("input:text, input:password, input:file, select").val("");
      $element
        .find("input:radio, input:checkbox")
        .removeAttr("checked")
        .removeAttr("selected");
      $element.find("select").prop("selectedIndex", 0);
      $element.find("input:text.form-autocomplete").each(function () {
        // Autocomplete fields add the value in a sibling hidden input
        // with the autocomplete id as the name.
        var id = $(this).attr("id");
        $(this)
          .siblings('input:hidden[name="' + id + '"]')
          .val("");
      });
    }
  }
})(jQuery);
