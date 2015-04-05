(function ($) {

  'use strict';

  var holdingsView = function(element)
  {
    this.$element = element;
    this.currentPage = 1;

    this.$next = this.$element.find('.next');
    this.$prev = this.$element.find('.prev');
    this.$results = this.$element.find('#repo-holdings-results');
    this.$currentPage = this.$element.find('#holdings-page');
    this.$resultStart = this.$element.find('#result-start');
    this.$resultEnd = this.$element.find('#result-end');

    this.url = this.$element.data('url');
    this.maxPage = this.$element.data('total-pages');

    this.init();
  };

  holdingsView.prototype =
  {
    constructor: holdingsView,
    init: function()
    {
      this.$element
        .on('mousedown', '.next', $.proxy(this.next, this))
        .on('mousedown', '.previous', $.proxy(this.prev, this));
    },

    next: function (e)
    {
      this.fetchResults(this.currentPage + 1);
    },

    prev: function (e)
    {
      this.fetchResults(this.currentPage - 1);
    },

    // Return next or previous page of results. fetchResults will return the next page
    // if getNext is true, or the previous page if false.
    fetchResults: function (page)
    {
      if (page < 1 || page > this.maxPage)
      {
        return;
      }

      $.ajax({
        url: this.url,
        context: this,
        dataType: 'json',
        data: { page: page },
        beforeSend: function()
          {
          },
        success: function (data)
          {
            this.$results.empty();

            for (var i = 0; i < data['holdings'].length; i++)
            {
              this.$results.append(
                $('<li>').append(
                  $('<a>').attr('href', data['holdings'][i]['url'])
                          .attr('title', data['holdings'][i]['title'])
                          .append(data['holdings'][i]['title'])
              ));
            }

            this.currentPage = page;

            this.$resultStart.empty();
            this.$resultEnd.empty();

            this.$resultStart.append(data['start']);
            this.$resultEnd.append(data['end']);
          },
        complete: function()
          {
          },
        error: function ()
          {
          }
        });
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
