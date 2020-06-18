(function ($) {
  Drupal.behaviors.autocomplete = {
    attach: function (context) {
      $('form:has(select.form-autocomplete)', context).each(function () {
        // Share <form/> with nested scopes
        var $form = $(this);

        // Keep a list of new releated resource forms that must be submitted
        // before $form so we can get the URIs after they are created
        var relatedResourceForms = [];

        // Add an item to the array, but prevent adding a duplicate item
        relatedResourceForms.add = function (item) {
          if (-1 == this.indexOf(item)) {
            this.push(item);
          }

          return this;
        }

        // Remove an item from the array, by value
        relatedResourceForms.remove = function (item) {
          var i = this.indexOf(item);

          if (i > -1) {
            this.splice(i, 1);
          }

          return this;
        }

        function done($relatedResourceForm) {
          // Remove $relatedResourceForm from stack
          relatedResourceForms.remove($relatedResourceForm);

          // If stack is empty, submit the main $form
          if (0 == relatedResourceForms.length) {
            $form.submit();
          }
        }

        // Submit forms to create new related resources (e.g. subjects)
        function submitRelatedResourceForms() {
          var count = 0;

          for (var i = 0; i < relatedResourceForms.length; i++) {
            relatedResourceForms[i].submit();

            count++;
          }

          // If no relatedResourceForms were submitted, continue with main form
          // submit by returning true
          if (0 == count) {
            return true;
          }

          // Prevent $form.submit() from continuing - final submit will be
          // triggered by done() function above
          return false;
        }

        function getRelatedResourceForm(
          $inputUser,
          $inputHidden,
          iframeSrc,
          rrFormInputId
        ) {
          // If relatedResourceForm has already been created for this $inputUser,
          // return it
          if ($inputUser.data('relatedResourceForm')) {
            // Make sure the related form is in the relatedResourceForms list
            relatedResourceForms.add($inputUser.data('relatedResourceForm'));

            return $inputUser.data('relatedResourceForm');
          }

          // Otherwise create a new iframe and load the web page (iframeSrc) that
          // provides the necessary form to create the new related resource
          return addRelatedResourceIframe(
            $inputUser,
            $inputHidden,
            iframeSrc,
            rrFormInputId
          );
        }

        /**
         * Create or link to an iframe containing a form which will be submitted
         * to create a new related resource (e.g. a new term). After submitting
         * the iframe form, get the related resource URI from the iframe, and
         * update the $inputHidden input with the URI value.
         *
         * @param {*} $inputUser The visible input (main form) which holds the
         *   user entered label for the new related resource
         * @param {*} $inputHidden The hidden input (main form) which is updated
         *   with the related resource URI after the iframe form is submitted
         *   to create the new related resource
         * @param {*} iframeSrc The URI of the AtoM page used to create the new
         *   related resource (e.g. /term/add), the <iframe> @src value
         * @param {*} rrFormInputId The $inputUser label is copied to this iframe
         *   input to set the label of the related resource (e.g. term name) on
         *   sumbit of the iframe form
         */

        function addRelatedResourceIframe(
          $inputUser,
          $inputHidden,
          iframeSrc,
          rrFormInputId
        ) {
          // Add hidden <iframe/>
          var $iframe = $('<iframe/>')
            .width(0)
            .height(0)
            .css('border', 0);

          // Wait until iframe contents have loaded so $input and $relatedResourceForm
          // selectors work
          $iframe.one('load', function () {
            // Get iframe > form > input for setting the new resource label
            var $input = $iframe.contents().find(rrFormInputId);

            // $($input[0].form) is apparently faster than $input.closest("form")
            var $relatedResourceForm = $($input[0].form);

            // Add one submit handler for each new choice. Use a named function
            // so it can be unbound if the choice is removed
            var submitCallback = function () {
              // After iframe form is submitted, wait for HTTP response to trigger
              // "load" event
              $iframe.one('load', function () {
                // Add the resulting page URI (e.g. "/newSubjectName") of the
                // response to the $inputHidden element
                $inputHidden
                  .val(this.contentWindow.document.location)
                  .trigger('change');

                // Submit $form when all relatedResourceForms have returned and
                // $inputHidden has been updated with the new URI
                done($relatedResourceForm);
              });

              // Update the iframe input's value with the user entered label
              $input.val($inputUser.val());

              $relatedResourceForm.submit();
            }

            // Add $relatedResourceForm to stack of forms to be submitted
            relatedResourceForms.add($relatedResourceForm);

            // Bind submit handler to fire on first form submit
            $relatedResourceForm.one('submit', submitCallback);

            // Store a link from this input to the relatedResourceForm
            $inputUser.data('relatedResourceForm', $relatedResourceForm);
          });

          // Set iframe source to load page *after* defining onload handler
          $iframe.attr('src', iframeSrc);

          // Add $iframe to bottom of HTML DOM
          $iframe.appendTo('body');

          return $relatedResourceForm;
        }

        // Submit any related resource forms and wait for response before
        // submitting the main $form
        $form.submit(function (event) {
          return submitRelatedResourceForms();
        });

        $('select.form-autocomplete', this).each(createYuiDiv);

        // Use $(document).on('loadFunctions') to add function in new rows created with multiRow.js
        $(document).on('loadFunctions', 'select.form-autocomplete', createYuiDiv);

        // This function help us to know if a .multiple select has
        // already a given value and highlight it if wished
        function multipleSelectHasMatches($listitems, val, opts) {
          var found = false;
          val = val.trim().toLowerCase();
          opts = opts || {};

          $listitems.each(function (i) {
            var $li = $(this);
            var text = $li.find('span').text() || $li.find('input[type=text]').val();

            if (val === text.trim().toLowerCase()) {
              found = true;

              if (opts.hasOwnProperty('highlight') || opts.highlight === true) {
                var $span = $('span', this);
                if ($span) {
                  $span.css('background', 'yellow');
                  setTimeout(function () {
                    $span.css('background', 'none');
                  }, 1000);
                }
              }

              // Stop .each()
              return false;
            }
          });

          return found;
        }

        function addMultiSelectItem($select, $ul, data) {
          // Only if not found we are adding a new item to the list
          if (multipleSelectHasMatches($ul.children('li'), data[0], { highlight: true })) {
            return;
          }

          // Make <li/> of hidden <input/> with item value, and
          // <span/> with item HTML
          //
          // Use XML() constructor to insert parsed HTML, works
          // for strings without an <element/> and strings with
          // one root <element/>, but not, I suspect, for
          // strings with multiple root <element/>s or text
          // outside the root <element/>
          var $li = $('<li title="Remove item">')
            .click(function () {
              // On click, remove <li/>
              $(this).hide('fast', function () {
                $(this).remove();

                // Toggle <ul/> based on children length
                // jQuery.toggle() expects a boolean parameter
                $ul.toggle(!!$ul.children().length);
              });
            });

          $li.appendTo($ul);

          // Use hidden input to store and POST URI of related resource
          var $hidden = $('<input name="' + $select.attr('name') + '" type="hidden" />');

          if (data[1]) {
            // If an existing resource was selected from the YUI autocomplete
            // $select then data[0] is the resource name, and data[1] is the URI
            $hidden.val(data[1]);  // Hidden URI
            $('<span>' + data[0] + '</span>').appendTo($li); // User-friendly label
          } else {
            // If a new "unmatched" value was entered in the YUI autocomplete,
            // then data[0] will be the string entered by the user

            // Clear hidden <input /> value, it will be set from the iframe
            // response
            $hidden.val('')

            // Make a new visible <input/> to display the user entered string
            var $input = $('<input type="text" class="yui-ac-input" />');
            $input
              // Set value to user entered string
              .val(data[0])

              // On blur
              .blur(function () {
                var $this = $(this);
                var val = $(this).val();
                if (multipleSelectHasMatches($li.siblings(), val)) {
                  val = '';
                }

                // if text is empty, remove <li/> and cancel addition of
                // new choice
                if (!val) {
                  $li.hide('fast', function () {
                    $(this).remove();
                  });

                  // Remove relatedResourceForm from stack
                  relatedResourceForms.remove($input.data('relatedResourceForm'));
                }
              })

              .click((function (event) {
                // Prevent parent <li /> click event from firing and removing
                // the element
                event.stopPropagation();
              }))

              .appendTo($li);
          }

          $hidden.appendTo($li);

          // Reveal the <ul />
          $ul.show()

          return $li;
        }

        // Use named function so it can be bound to events
        function createYuiDiv() {
          // Share <select/> with nested scopes
          var $select = $(this);

          // Make autocomplete <input/>, copy @class from <select/>, copy
          // @id from <select/> so <label for="..."/> is correct
          var $input = $('<input type="text" class="' + $(this).attr('class') + '" id="' + $(this).attr('id') + '"/>').insertAfter(this);

          if ($(this).attr('multiple')) {
            // If multiple <select/>, make <ul/> of selected <option/>s
            var $ul = $('<ul/>').hide().insertAfter(this);

            $('option:selected', this).each(function () {
              // Make <li/> of hidden <input/> with <option/> value, and
              // <span/> with <option/> HTML contents
              $('<li title="Remove item"><input name="' + $select.attr('name') + '" type="hidden" value="' + $(this).val() + '"/><span>' + $(this).html() + '</span></li>')
                .click(function () {
                  // On click, remove <li/> and hide <ul/> if has not siblings
                  $(this).hide('fast', function () {
                    $(this).remove();

                    // Toggle <ul/> based on children length
                    // jQuery.toggle() expects a boolean parameter
                    $ul.toggle(!!$ul.children().length);
                  });
                })
                .appendTo($ul.show());
            });
          }
          else {
            // If single <select/>, make one hidden <input/> with
            // <option/> value,
            // TODO Upgrade to jQuery 1.4.3,
            // http://dev.jquery.com/ticket/5163
            var $hidden = $('<input name="' + $(this).attr('name') + '" type="hidden" value="' + ($(this).val() ? $(this).val() : '') + '"/>').insertAfter(this);

            $input

              // Copy <option/> value to autocomplete <input/>
              .val($('option:selected', this).text())

              // Give user chance to remove a selection, in case the text
              // field is completely removed, hidden value is cleared.
              .change(function () {
                if (!$input.val().length) {
                  $hidden.val('').trigger('change');
                }
              });
          }

          // A following sibling with class .list and a value specifies
          // that autocomplete items can be requested dynamically from
          // the specified URI
          //
          // NOTE: $('~ .list', this) stopped working in jQuery 1.4.4
          var value = $(this).siblings('.list').val();
          if (value) {
            var uri, rrFormInputId;

            // Split into URI and selector like jQuery load()
            [uri, rrFormInputId] = value.split(' ', 2);

            var dataSource = new YAHOO.util.XHRDataSource(uri);

            // Cache at least one query so autocomplete items are only
            // requested if the value of the autocomplete <input/>
            // changes
            dataSource.maxCacheEntries = 1;

            dataSource.responseType = YAHOO.util.DataSourceBase.TYPE_HTMLTABLE;

            // Overriding doBeforeParseData() and doBeforeCallback()
            // not powerful enough, override parseHTMLTableData() and
            // skip isArray(fields) check
            dataSource.parseHTMLTableData = function (request, response) {
              var results = [];
              $('tbody tr', response).each(function () {
                // For each item, select HTML contents and @href of
                // <a/> in first cell
                results.push([$('td a', this).html(), $('td a', this).attr('href')]);
              });

              // Storing the results so we can use them later
              $select.data('xhrResults', results);

              return { results: results };
            };
          }
          else {
            // Otherwise add each enabled <option/> to static list of
            // items
            var dataSource = new YAHOO.util.LocalDataSource();

            // :enabled removed, it is broken in Chrome, see issue 2348
            // See also http://bugs.jquery.com/ticket/11872
            $('option', this).each(function () {
              if ($(this).val()) {
                // For each item, select HTML contents and value of
                // <option/>
                //
                // Selecting HTML contents is important for
                // <em>Untitled</em>
                dataSource.liveData.push([$(this).html(), $(this).val()]);
              }
            });
          }

          // Can't figure out how to get access to the DOM event which
          // triggered a custom YUI event, so bind a listener to the
          // interesting DOM event before instantiating the YUI widget,
          // to save the DOM event before triggering custom YUI events
          //
          // TODO File YUI issue for this
          var event;
          $input.keydown(function () {
            event = arguments[0];
          })

          var autoComplete = new YAHOO.widget.AutoComplete($input[0], $('<div/>').insertAfter(this)[0], dataSource);

          // Display up to 20 results in the container
          autoComplete.maxResultsDisplayed = 20;

          // Show some items even if user types nothing,
          // http://developer.yahoo.com/yui/autocomplete/#minquery
          autoComplete.minQueryLength = 0;

          // Give user chance to type something, one second may still
          // be too little,
          // http://developer.yahoo.com/yui/autocomplete/#delay
          autoComplete.queryDelay = parseFloat($select.data('autocomplete-delay')) || 1;

          // Add other fields from the form to the autocomplete request
          if ($(this).attr('name') == 'relatedAuthorityRecord[subType]') {
            autoComplete.generateRequest = function (query) {
              var parent = $('#relatedAuthorityRecord_type').val();

              return '&parent=' + parent + '&query=' + query;
            };
          }
          else if (($(this).attr('name') == 'parent' || $(this).attr('name') == 'relatedTerms[]') && $(this).siblings('.list').val().indexOf('/term/autocomplete') != -1) {
            autoComplete.generateRequest = function (query) {
              var taxonomy = $('input[name=taxonomy]').val();

              return '?taxonomy=' + taxonomy + '&query=' + query;
            };
          }
          else if ($(this).attr('name') == 'converseTerm' && $(this).siblings('.list').val().indexOf('/term/autocomplete') != -1) {
            autoComplete.generateRequest = function (query) {
              var taxonomy = $('input[name=taxonomy]').val();
              var parent = $('input[name=parent]').val();

              return '?taxonomy=' + taxonomy + '&parent=' + parent + '&query=' + query;
            };
          }
          else if ($(this).attr('name') == 'collection' && $(this).closest('section.advanced-search').length) {
            autoComplete.generateRequest = function (query) {
              var repository = $('section.advanced-search select[name=repos]').val();

              return '&repository=' + repository + '&query=' + query;
            };
          }
          // Alternatively use try/catch?
          else if ('undefined' !== typeof dataSource.liveData.indexOf
            && -1 != dataSource.liveData.indexOf('?')) {
            autoComplete.generateRequest = function (query) {
              return '&query=' + query;
            };
          }

          // Start throbbing when first query is sent, stop throbbing
          // when the last query to be sent is complete
          //
          // TODO This binds too many listeners and doesn't actually
          // detect the last query to be sent : (
          var id = 0;
          autoComplete.dataRequestEvent.subscribe(function () {
            var thisId = ++id;

            $input.addClass('throbbing');

            autoComplete.dataReturnEvent.subscribe(function () {
              if (id == thisId) {
                $input.removeClass('throbbing');
              }
            });
          });

          autoComplete.itemSelectEvent.subscribe(function (type, args) {
            selectItem.call(undefined, args[2]);
          });

          // Callback function used when itemSelectEvent is fired but also
          // used when textboxBlurEvent under certain circumstances
          var selectItem = function (data) {
            if ($select.attr('multiple')) {
              // Cancel default action of saved DOM event so as not
              // to loose focus when selecting multiple items
              if (event) {
                event.preventDefault();
              }

              addMultiSelectItem($select, $ul, data);

              // Select autocomplete <input/> contents so typing will
              // replace it
              $input.select();
            }
            else {
              // On single <select/> item select, simply update the
              // value of this input
              $hidden.val(data[1]).trigger('change');
            }

            // Update the value of the autocomplete <input/> here
            // with text of parsed HTML, instead of source
            //
            // Use XML() constructor as with multiple <select/>, but
            // use toString() to get text of parsed HTML
            if (data[0].indexOf('<b>') >= 0 && data[0].indexOf('</b>') >= 0) {
              // Remove bold tags
              $input.val(data[0].substring(0, data[0].indexOf('<b>'))
                + data[0].substring(data[0].indexOf('<b>') + 3, data[0].indexOf('</b>'))
                + data[0].substring(data[0].indexOf('</b>') + 4, data[0].length));
            }
            else {
              $input.val(data[0]);
            }
          };

          // Reuse autocomplete's suggested value when the user entried
          // the same text in order to avoid duplicates.
          if (!$select.attr('multiple')) {
            autoComplete.textboxBlurEvent.subscribe(function () {
              var val = $input.val().trim().toLowerCase()
              var results = $select.data('xhrResults') || [];
              if (val && val.length && results.length) {
                for (var i = 0; i < results.length; i++) {
                  if (results[i][0].trim().toLowerCase() === val) {
                    selectItem(results[i]);

                    break;
                  }
                }
              }
            });
          }

          // If multiple <select/>, clear autocomplete <input/> on
          // blur
          //
          // TODO Don't clear if event.preventDefault() was called?
          if ($select.attr('multiple')) {
            autoComplete.textboxBlurEvent.subscribe(function () {
              $input.val('');
            });
          }

          // Show autocomplete items, use named function so it can be
          // bound to click and focus events, use private _nDelayID to
          // cancel query on e.g. keypress before query delay
          //
          // TODO File YUI issue for this
          function sendQuery() {
            if (-1 == autoComplete._nDelayID) {
              autoComplete._nDelayID = setTimeout(function () {
                autoComplete._sendQuery(autoComplete.getInputEl().value);
              }, autoComplete.queryDelay * 1000);
            }
          }

          // Use custom YUI event to avoid DOM focus events triggered
          // by YUI widget interaction
          autoComplete.textboxFocusEvent.subscribe(sendQuery);

          // Listen for click to show autocomplete items after
          // selecting existing item, but not changing focus
          $input.click(sendQuery);

          // A following sibling with class .add and a value specifies
          // that new choices can be added to this input with a form at
          // the specified URI, by copying the value of the
          // autocomplete <input/> to the element at the specified
          // selector
          //
          // Use <iframe/>s instead of XHR because I can't figure out
          // how to get access to the Location: header of redirect
          // responses, and can't figure out how to get access to the
          // URI of the final response,
          // http://www.w3.org/TR/XMLHttpRequest/#notcovered
          var $add = $(this).siblings('.add'); // $('~ .add', this) stopped working in jQuery 1.4.4
          var value = $add.val();
          if (value) {
            // Split into URI and selector like jQuery load()
            var uri, rrFormInputId;

            [uri, rrFormInputId] = value.split(' ', 2);

            // Support for data-link-existing="true"
            if ($add.data('link-existing') === true) {
              var u = new URI(uri);
              u.addQuery('linkExisting', true);
              uri = u.toString();
            }

            // The following applies to both single and multiple <select/>
            autoComplete.unmatchedItemSelectEvent.subscribe(function () {
              var $relatedResourceForm;

              // Stop throbbing
              $input.removeClass('throbbing');

              if ($input.val()) {
                if (!$select.attr('multiple')) {
                  // Create iframe which will be submitted to create a new
                  // related resource from the "unmatched" value
                  $relatedResourceForm = getRelatedResourceForm($input, $hidden, uri, rrFormInputId);
                } else {
                  // Cancel default action of saved DOM event so as
                  // not to loose focus when selecting multiple items
                  if (event) {
                    event.preventDefault();
                  }

                  var $li = addMultiSelectItem($select, $ul, [$input.val()]);

                  // Select autocomplete <input/> contents so typing
                  // will replace it
                  $input.select();

                  // Get or create form (in an iframe) to create the related
                  // resource
                  $relatedResourceForm = getRelatedResourceForm(
                    $li.find('input[type=text]'),
                    $li.find('input[type=hidden]'),
                    uri,
                    rrFormInputId
                  );
                }
              }
              else {
                $hidden.val('').trigger('change');

                // If unmatched item is empty, remove form from stack
                relatedResourceForms.remove($relatedResourceForm);
              }
            });

            if (!$select.attr('multiple')) {
              // Selecting existing item cancels addition of a new
              // choice
              autoComplete.itemSelectEvent.subscribe(function () {
                // Remove form from stack
                relatedResourceForms.remove($relatedResourceForm);

                // Trigger event to load item data if it's needed
                $input.trigger({
                  type: 'itemSelected',
                  itemValue: $hidden.attr('value')
                });
              });
            }
          }
          else {
            // Otherwise new choices can't be added to this input,
            // http://developer.yahoo.com/yui/autocomplete/#force

            // Clear both autocomplete <input/> and hidden <input/>
            autoComplete.unmatchedItemSelectEvent.subscribe(function () {
              $hidden.val('').trigger('change');
              $input.val('');
            });
          }

          // Finally remove <select/> element
          $(this).remove();
        }

        // Fix z-index autocomplete bug in IE6/7
        // See http://developer.yahoo.com/yui/examples/autocomplete/ac_combobox.html
        if ($.browser.msie && $.browser.version < 8) {
          $('.form-item.yui-ac').each(function (index) {
            this.style.zIndex = 20100 - index;
          });
        }
      });
    }
  };
})(jQuery);
