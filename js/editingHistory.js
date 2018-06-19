"use strict";

(function ($) {

  var page = 1;

  $(loadEditingHistory);

  function startActivity()
  {
    $('#editingHistoryActivityIndicator').removeClass('hidden');
  }

  function endActivity()
  {
    $('#editingHistoryActivityIndicator').addClass('hidden');
  }

  function changePage(amount)
  {
    page = page + amount;
    getAndAppendItems($('#editingHistoryRows'), '/user/editingHistory', page);
  }

  function constructRowElement(rowData)
  {
   var row = $('<tr>');
    var td = $('<td></td>').append($('<a></a>').attr('href', rowData.slug).text(rowData.title));
    var td2 = $('<td></td>').text(rowData.createdAt);
    var td3 = $('<td></td>').text(rowData.actionType);

    return row.append(td).append(td2).append(td3);
  }

  function getAndAppendItems(tbodyEl, url, page)
  {
    // Assemble query and creation queue
    var queryString = "?page=" + page;
    var pagedUrl = window.location.pathname + url + queryString;

    // Get and append items
    startActivity();
    $.ajax({
      url: pagedUrl,
      success: function(dataRaw) {
        var data = JSON.parse(dataRaw);

        // Empty table body then add rows using results
        tbodyEl.empty();

        data.results.forEach(function(item) {
          tbodyEl.append(constructRowElement(item));
        });

        // Hide or show previous page button
        $('#previousButton').toggleClass('hidden', page <= 1);

        // Hide of show next page button
        $('#nextButton').toggleClass('hidden', data.pages <= page);

        // Hide or show next page button
        $('#nextButton').toggle(data.pages > page);

        // Only show link to expand editing history section if data exists
        if (data.pages)
        {
          $('#editingHistory').removeClass('hidden');
        }

        endActivity();
      }
    });
  }

  function loadEditingHistory()
  {
    // Activate button to navigate to previous page
    $('#previousButton').click(function() {
      changePage(-1);
    });

    // Activate button to navigate to next page
    $('#nextButton').click(function() {
      changePage(1);
    });

    // Load first page
    changePage(0);
  }
})(jQuery);
