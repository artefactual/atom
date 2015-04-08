(function ($) {

  'use strict';

  var holdingsView = function(element)
  {
    this.$element = element;

    this.url = this.$element.data('url');

    this.currentPage = 1;
    this.totalPages = parseInt(this.$element.data('total-pages'), 10);

    // Stop execution when pagination is not needed
    if (this.totalPages < 2)
    {
      return;
    }

    this.$prev = this.$element.find('.prev').prop('disabled', true);
    this.$next = this.$element.find('.next');
    this.$pageInput = this.$element.find('#sidebar-pager-input');

    this.$results = this.$element.find('ul');
    this.$spinner = this.$element.find('#spinner');
    this.$resultStart = this.$element.find('.result-start');
    this.$resultEnd = this.$element.find('.result-end');

    // Threshold for the spinning timer and page input
    this.BUSY_THRESHOLD = 200;
    this.PAGE_TYPING_THRESHOLD = 650;

    this.init();
  };

  holdingsView.prototype =
  {
    constructor: holdingsView,
    init: function()
    {
      this.$next.on('mousedown', $.proxy(this.next, this));
      this.$prev.on('mousedown', $.proxy(this.prev, this));
      this.$pageInput.on('keyup', $.proxy(this.pageTyping, this));
    },

    next: function (e)
    {
      this.fetchResults(this.currentPage + 1);
    },

    prev: function (e)
    {
      this.fetchResults(this.currentPage - 1);
    },

    // Setter/getter of busy state
    busy: function (busy)
    {
      // Getter
      if (typeof busy === 'undefined') {
        return this._busy;
      }

      // Setter
      this._busy = busy;
      this.$next.toggleClass('disabled', busy);
      this.$prev.toggleClass('disabled', busy);

      if (busy)
      {
        var $spinner = this.$spinner;

        this.busyTimer && clearTimeout(this.busyTimer);
        this.busyTimer = setTimeout(function ()
          {
            $spinner.removeClass('hidden').show();
          }, this.BUSY_THRESHOLD);
      }
      else
      {
        clearTimeout(this.busyTimer);
        this.$spinner.hide();
      }
    },

    // Fetch items for a given page and inject the results in the DOM
    fetchResults: function (page)
    {
      if (this.busy())
      {
        return;
      }

      if (page < 1 || page > this.totalPages)
      {
        this.$pageInput.prop('value', this.currentPage);
        return;
      }

      $.ajax({
        url: this.url,
        type: 'GET',
        context: this,
        dataType: 'json',
        data: { page: page },
        beforeSend: function()
          {
            this.busy(true);
          },
        success: function (data)
          {
            this.currentPage = page;
            this.$pageInput.prop('value', page);

            this.$results.empty();

            var len = data['holdings'].length
            for (var i = 0; i < len; i++)
            {
              this.$results.append(
                $('<li>').append(
                  $('<a>').attr('href', data['holdings'][i]['url'])
                          .attr('title', data['holdings'][i]['title'])
                          .append(data['holdings'][i]['title'])));
            }

            this.$resultStart.html(data['start']);
            this.$resultEnd.html(data['end']);

            // Enable/disable prev/next buttons according to the current page
            this.$prev.prop('disabled', this.currentPage == 1);
            this.$next.prop('disabled', this.currentPage == this.totalPages);
          },
        complete: function()
          {
            this.busy(false);
          }
        });
    },

    pageTyping: function() {
      var fetchResults = $.proxy(this.fetchResults, this);
      var page = parseInt(this.$pageInput.prop('value'));

      this.pageTimer && clearTimeout(this.pageTimer);
      this.pageTimer = setTimeout(function ()
        {
          fetchResults(page);
        }, this.PAGE_TYPING_THRESHOLD);
      }
  };

  $.fn.holdings = function()
    {
      var $this = this;
      var data = $this.data('holdings');
      if (!data)
      {
        $this.data('holdings', new holdingsView(this));
      }
    };

  $.fn.holdings.Constructor = holdingsView;

  $(function ()
    {
      var $holdings = $('#repo-holdings');

      if (0 < $holdings.length)
      {
        $holdings.holdings();
      }
    });

})(jQuery);
