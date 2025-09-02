(function ($) {
  "use strict";

  $(() =>
    $(".sidebar-paginated-list").each(
      (_, element) => new PaginatedListView($(element))
    )
  );

  // Thresholds for the spinning timer and page input.
  const BUSY_THRESHOLD = 200;
  const PAGE_TYPING_THRESHOLD = 650;

  class PaginatedListView {
    constructor($element) {
      this.$element = $element;
      this.url = this.$element.data("url");

      this.currentPage = 1;
      this.totalPages = parseInt(this.$element.data("total-pages"), 10);

      // Stop execution when pagination is not needed
      if (this.totalPages < 2) {
        return;
      }

      this.$prev = this.$element.find(".page-link-prev");
      this.$next = this.$element.find(".page-link-next");
      this.updatePageLinkState(this.$prev, true);

      this.$pageInput = this.$element.find("input[type=number]");
      this.$results = this.$element.find("> ul");
      this.$spinner = this.$element.find(".spinner");
      this.$resultStart = this.$element.find(".result-start");
      this.$resultEnd = this.$element.find(".result-end");

      this.init();
    }
    init() {
      this.$next.on("click", this.next.bind(this));
      this.$prev.on("click", this.prev.bind(this));
      this.$pageInput.on("change", this.change.bind(this));
    }
    next(e) {
      e.preventDefault();
      this.fetchResults(this.currentPage + 1);
    }
    prev(e) {
      e.preventDefault();
      this.fetchResults(this.currentPage - 1);
    }
    // Setter/getter of busy state
    busy(busy) {
      // Getter
      if (typeof busy === "undefined") {
        return this._busy;
      }

      // Setter
      this._busy = busy;

      var $spinner = this.$spinner;

      if (busy) {
        this.updatePageLinkState(this.$prev, true);
        this.updatePageLinkState(this.$next, true);

        this.busyTimer && clearTimeout(this.busyTimer);
        this.busyTimer = setTimeout(function () {
          $spinner.removeClass("d-none");
        }, BUSY_THRESHOLD);
      } else {
        clearTimeout(this.busyTimer);
        $spinner.addClass("d-none");
        this.updatePageLinkState(this.$prev, this.currentPage == 1);
        this.updatePageLinkState(
          this.$next,
          this.currentPage == this.totalPages
        );
      }
    }
    // Fetch items for a given page and inject the results in the DOM
    fetchResults(page) {
      if (this.busy()) {
        return;
      }

      if (page < 1 || page > this.totalPages) {
        this.$pageInput.prop("value", this.currentPage);
        return;
      }

      $.ajax({
        url: this.url,
        type: "GET",
        context: this,
        dataType: "json",
        data: { page: page },
        beforeSend: function () {
          this.busy(true);
        },
        success: function (data) {
          this.currentPage = page;
          this.$pageInput.prop("value", page);

          this.$results.empty();

          var len = data["results"].length;
          for (var i = 0; i < len; i++) {
            this.$results.append(
              $('<a class="list-group-item list-group-item-action">')
                .attr("href", data["results"][i]["url"])
                .attr("title", data["results"][i]["title"])
                .append(data["results"][i]["title"])
            );
          }

          this.$resultStart.html(data["start"]);
          this.$resultEnd.html(data["end"]);
        },
        complete: function () {
          this.busy(false);
        },
      });
    }
    change(event) {
      var fetchResults = this.fetchResults.bind(this);
      var page = parseInt(this.$pageInput.prop("value"));

      this.pageTimer && clearTimeout(this.pageTimer);
      this.pageTimer = setTimeout(function () {
        fetchResults(page);
      }, PAGE_TYPING_THRESHOLD);
    }
    updatePageLinkState($elem, disabled) {
      $elem.parent().toggleClass("disabled", disabled);
      $elem.attr("aria-disabled", disabled);
    }
  }
})(jQuery);
