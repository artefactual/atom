(function ($) {
  $(document).ready(function() {
    // Add validator to ensure a related donor is selected/created
    var validator = function () {
      var relation = $('#relatedDonor_resource').val();

      if (!relation.length) {
        // Display error message
        $('#relatedDonorError').css('display', 'block');

        return false;
      } else {
        // Hide error message until required again
        $('#relatedDonorError').css('display', 'none');
      }
    }

    // Hide error on cancel
    var afterCancelLogic = function () {
      $('#relatedDonorError').css('display', 'none');
    }

    var $newRowTemplate = $('#\\{relatedDonor\\[id\\]\\}');

    // Remove row template from DOM
    $newRowTemplate.detach();
    $newRowTemplate.removeClass('hidden');

    // Define dialog
    var dialog = new QubitDialog('relatedDonor',
      {
        'displayTable': 'relatedDonorDisplay',
        'newRowTemplate': $newRowTemplate[0].outerHTML,
        'validator': validator,
        'afterCancelLogic': afterCancelLogic,
        'relationTableMap': function (response) {
          response.resource = response.object;
          response.resourceDisplay = response.objectDisplay;

          return response;
        }
      }
    );

    // Add edit button to pre-existing donor rows
    var editImgTag = $newRowTemplate.find('img[src="/images/pencil.png"]');
    $('#relatedDonorDisplay tr[id]')
      .click(function () {
        dialog.open(this.id);
      })
      .find('td:last')
      .prepend(editImgTag);

    // Clear donor info when the selected donor is changed
    var unselect = function() {
      dialog.clear();
      $('#relatedDonor .yui-ac-input').off('input', unselect);
    }

    // Load primary contact data when a new item is selected.
    // Can't use dialog.loadData() with the donor primary contact url
    // as it loads the data with a different id. The contact data must
    // be obtained first and then update the dialog using the current dialog id
    $('#relatedDonor .yui-ac-input').on('itemSelected', function (e) {
      var dataSource = new YAHOO.util.XHRDataSource(e.itemValue + '/donor/primaryContact');
      dataSource.responseType = YAHOO.util.DataSourceBase.TYPE_JSON;
      dataSource.parseJSONData = function (request, response) {
        response = dialog.options.relationTableMap.call(dialog, response);

        return {
          results: [new (function (response) {
            for (i in response) {
              this[dialog.fieldPrefix + '[' + i + ']'] = response[i];
            }
          })(response)]
        };
      }

      dataSource.sendRequest(null, {
        success: function (request, response) {
          dialog.updateDialog(dialog.id, response.results[0]);

          // Clear donor info if donor name is changed
          $('#relatedDonor .yui-ac-input').on('input', unselect);
        }
      });
    });
  });
})(jQuery);
