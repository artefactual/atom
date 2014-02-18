(function ($)
  {
    QubitDialog = function (table, options)
      {
        this.table = document.getElementById(table);
        this.$form = $(this.table).closest('form'); // Parent form
        this.instances = 0; // Counter
        this.label = $(':header', this.table).remove().text();
        this.fields = [];
        this.initialValues = [];
        this.data = {};
        this.deletes = [];
        this.options = options;
        this.fieldNameFilter = /(\w+)\[(\w+)\]/;
        this.fieldPrefix = null;
        this.iframes = [];
        this.count = 0;

        var thisDialog = this;

        /*
         * Initialize
         */

        // Find field prefix (if there is one)
        var matches = $(':input[name]:first', thisDialog.table).attr('name').match(this.fieldNameFilter);
        if (null != matches)
        {
          this.fieldPrefix = matches[1];
        }

        // Build an internal representation of HTML table elements
        $(':input', thisDialog.table).each(function ()
          {
            if ('' === this.name || undefined !== thisDialog.fields[this.name])
            {
              return;
            }

            // Store initialValue of element
            switch (this.type)
            {
              case 'radio':
              case 'checkbox':
                thisDialog.fields[this.name] = [];
                thisDialog.initialValues[this.name] = $('input[name="' + this.name + '"]', thisDialog.table).each(function ()
                    {
                      thisDialog.fields[this.name].push(this);
                    })
                  .filter(':checked').val();

                break;

              default:
                thisDialog.fields[this.name] = this;
                thisDialog.initialValues[this.name] = this.value;
            }
          });

        // Bind click event to "Add" link
        $('<a class="btn btn-small" href="#">Add new</a>')
          .click(function (event)
            {
              // Prevent default action, "go to top of page"
              event.preventDefault();

              thisDialog.open();
            })
          .insertAfter(this.table);

        // Enable/disable relatedAuthorityRecord_subType field
        $('select[id=relatedAuthorityRecord_type]', thisDialog.table)
          .change(function ()
            {
              var subTypeField = $('input[id=relatedAuthorityRecord_subType]', thisDialog.table);

              if (this.value == '')
              {
                subTypeField.prop('disabled', 'disabled');
              }
              else
              {
                subTypeField.prop('disabled', false).focus();
              }
          });

        // Create YUI container for dialog
        var $yuiDialogWrapper = $('<div id="' + this.table.id + '">'
          + '  <div class="hd">'
          + '    ' + this.label
          + '  </div><div class="bd">'
          + '    <form/>'
          + '  </div>'
          + '</div>').appendTo('body');

        // Set overflow and height if options.height provided
        if (undefined !== thisDialog.options.height)
        {
          $yuiDialogWrapper.find('.bd')
            .css('overflow', 'auto')
            .height(thisDialog.options.height);
        }

        // Replace dialog table with "Add" link and move into dialog wrapper
        $(this.table).appendTo($yuiDialogWrapper.find('form'));

        // Submit dialog data
        var handleYuiSubmit = function ()
          {
            this.hide(); // Hide dialog

            var data = this.getData();

            // The validator must return false to cancel the operation
            if ('undefined' !== typeof thisDialog.options.validator
                && false === thisDialog.options.validator.call(this, data))
            {
              return false;
            };

            thisDialog.submit(data); // Save dialog data
          }

        var handleYuiCancel = function ()
          {
            this.cancel(); // Cancel YUI submit
            thisDialog.clear(); // Clear dialog fields
          }

        this.yuiDialog = new YAHOO.widget.Dialog($yuiDialogWrapper[0], {
          buttons: [{ text: 'Submit', handler: handleYuiSubmit, isDefault: true },
            { text: 'Cancel', handler: handleYuiCancel }],
          fixedcenter: true,
          modal: true,
          postmethod: 'none',
          visible: false,
          zIndex: 20000 });

        // Render TabView from markup if exists (for example: relatedContactInformation)
        var $tabview = $yuiDialogWrapper.find('.yui-navset');
        if ($tabview.length)
        {
          this.yuiDialog.tabview = new YAHOO.widget.TabView($tabview[0]);
        }

        this.yuiDialog.render();

        this.yuiDialog.cfg.queueProperty('keylisteners', new YAHOO.util.KeyListener(this.yuiDialog.form, { keys: 27 }, {
          fn: handleYuiCancel,
          scope: this.yuiDialog,
          correctScope: true }).enable());

        // Remove all showEvent listeners to prevent default "focusFirst"
        // behavior
        this.yuiDialog.showEvent.unsubscribeAll();
        this.yuiDialog.showEvent.subscribe(function ()
          {
            if (undefined !== Drupal.behaviors.date)
            {
              Drupal.behaviors.date.attach(thisDialog.table.parentNode);
            }

            if (undefined !== thisDialog.options.showEvent)
            {
              thisDialog.options.showEvent.call(this);
            }
          });

        // Append hidden fields to form on submit
        this.onSubmit = function (event)
          {
            thisDialog.appendHiddenFields();

            // Issue 1782
            if (!thisDialog.iframes.length)
            {
              return;
            }

            event.preventDefault();

            // Apply selector to <iframe/> contents, update value of selected
            // element with value of the autocomplete <input/>, and submit
            // selected element's form
            var iframe;
            while (iframe = thisDialog.iframes.shift())
            {
              thisDialog.count++;

              $($(iframe.selector, iframe.iframe[0].contentWindow.document).val(iframe.value)[0].form).submit();
            }

            // If no iframes, just submit
            if (0 === thisDialog.count)
            {
              thisDialog.done();
            }
          }

        // Bind submit method
        this.$form.submit(this.onSubmit);

        // Wait for all iframes to finish before submitting main form
        this.done = function ()
          {
            // Decrement count of listeners and submit if all done
            if (1 > --this.count)
            {
              thisDialog.$form

                // Unbind submit listeners to avoid triggering again
                .unbind('submit', this.onSubmit)

                .submit();
            }
          }

        /*
         * Methods
         */
        this.renderField = function (fname)
          {
            if ($(this.fields[fname]).next().hasClass('form-autocomplete'))
            {
              // Autocomplete text
              return $(this.fields[fname]).next().val();
            }
            else if ('select-one' === this.fields[fname].type || 'select-multi' === this.fields[fname].type)
            {
              // Select box text
              return $(this.fields[fname]).children(':selected').text();
            }
            else if ('radio' === this.fields[fname].type)
            {
              return $('input[name=' + fname + ']:checked', thisDialog.table).val();
            }
            else if (0 < this.fields[fname].length)
            {
              return '<input type="checkbox" disabled="disabled"' + (this.fields[fname][0].checked ? ' checked="checked"' : '') + ' />';
            }
            else if (undefined !== this.fields[fname].value)
            {
              return this.fields[fname].value;
            }

            return '';
          }

        /**
         * Helper to get a field that has a prefix (e.g. formname[myField]) without
         * specifying the prefix name
         */
        this.getField = function (fname)
          {
            if (null != this.fieldPrefix && null == fname.match(this.fieldNameFilter))
            {
              var fullname = this.fieldPrefix + '[' + fname + ']';

              return this.fields[fullname];
            }

            return this.fields[fname];
          }

        this.open = function (id)
          {
            // Disable relatedAuthorityRecord_subType field. The property is removed on YUI autocomplete first load
            var subTypeField = $('input[id=relatedAuthorityRecord_subType]', thisDialog.table);
            if (subTypeField.length > 0)
            {
              subTypeField.prop('disabled', 'disabled');
            }

            this.id = id;
            if (undefined === this.id)
            {
              // If no "id" passed as argument then create unique id and skip
              // the data load
              this.id = 'new' + this.instances++;

              this.yuiDialog.show();
              this.yuiDialog.focusFirst();

              return;
            }

            this.loadData(this.id, function ()
              {
                thisDialog.yuiDialog.show();
              });
          }

        this.loadData = function (id, callback)
          {
            if (undefined !== this.data[id])
            {
              this.updateDialog(id, this.data[id], callback)
            }
            else
            {
              // TODO Ajax call to get relation data from database
              var dataSource = new YAHOO.util.XHRDataSource(id);
              dataSource.responseType = YAHOO.util.DataSourceBase.TYPE_JSON;
              dataSource.parseJSONData = function (request, response)
                {
                  if (undefined !== thisDialog.options.relationTableMap)
                  {
                    response = thisDialog.options.relationTableMap.call(thisDialog, response);
                  }

                  return { results: [new (function (response)
                    {
                      for (name in response)
                      {
                        this[thisDialog.fieldPrefix + '[' + name + ']'] = response[name];
                      }
                    })(response)] };
                }

              dataSource.sendRequest(null, {
                success: function (request, response)
                  {
                    thisDialog.updateDialog(id, response.results[0], callback);
                  } });
            }
          }

        this.updateDialog = function (thisId, thisData, callback)
          {
            if (undefined === this.data[thisId])
            {
              this.data[thisId] = thisData;
            }

            for (fieldname in this.fields)
            {
              if (null == thisData[fieldname])
              {
                continue;
              }

              if (jQuery.isArray(thisData[fieldname]))
              {
                thisData[fieldname] = thisData[fieldname][0];
              }

              if ('boolean' === typeof thisData[fieldname])
              {
                jQuery(this.fields[fieldname]).prop('checked', thisData[fieldname]);
              }
              else if ('string' === typeof thisData[fieldname])
              {
                this.fields[fieldname].value = thisData[fieldname];
              }

              // Get display value for autocompletes
              if ($(this.fields[fieldname])
                  .next('input')
                  .hasClass('form-autocomplete'))
              {
                var hiddenInput = this.fields[fieldname];

                // First check if a "Display" value is include in "thisData"
                var displayField = fieldname.substr(0, fieldname.length - 1) + 'Display]';
                if (undefined !== thisData[displayField])
                {
                  $(hiddenInput).next('input').val(jQuery.trim(thisData[displayField]));
                }
                else if (0 < hiddenInput.value.length)
                {
                  // If necessary, get name via Ajax request to show page
                  var dataSource = new YAHOO.util.XHRDataSource(hiddenInput.value);
                  dataSource.responseType = YAHOO.util.DataSourceBase.TYPE_TEXT;
                  dataSource.parseTextData = function (request, response)
                    {
                      return { 'results' : [ $(response).find('.label, #main-column h1').text().trim() ] };
                    };

                  // Set visible input field of yui-autocomplete
                  dataSource.sendRequest(null, {
                    scope: $(hiddenInput),
                    success: function (request, response)
                      {
                        if (this.attr('name') == 'relatedAuthorityRecord[subType]')
                        {
                          // Set value + actor name for subType field
                          this
                            .next('.form-autocomplete')
                            .val(response.results[0] + thisData['relatedAuthorityRecord[actor]']);
                        }
                        else
                        {
                          this
                            .next('.form-autocomplete')
                            .val(response.results[0]);
                        }
                      } });
                }
              }

              // Enable relatedAuthorityRecord_subType field if there is type data
              if (fieldname == 'relatedAuthorityRecord[type]')
              {
                $('input[id=relatedAuthorityRecord_subType]', thisDialog.table).prop('disabled', false);
              }
            }

            if (undefined !== callback)
            {
              callback();
            }
          }

        this.save = function (yuiDialogData)
          {
            $('input.form-autocomplete', thisDialog.table).each(function ()
              {
                $hidden = $(this).prev('input:hidden');

                // Test for existing <iframe/>
                for (var i in thisDialog.iframes)
                {
                  if (thisDialog.id === thisDialog.iframes[i].dialogId && $hidden.attr('name') === thisDialog.iframes[i].inputName)
                  {
                    var index = i;
                  }
                }

                // Test if autocomplete has a value
                if (0 < this.value.length)
                {
                  // If no URI is set, then selecting unmatched value
                  if (0 === $hidden.val().length)
                  {
                    // Allowing adding new values via iframe
                    var value = $('~ .add', this).val();
                    if (value)
                    {
                      var components = value.split(' ', 2);

                      if (undefined === index)
                      {
                        // Add hidden <iframe/> for each new choice
                        $iframe = $('<iframe src="' + components[0] + '"/>')
                          .width(0)
                          .height(0)
                          .css('border', 0)
                          .appendTo('body');

                        thisDialog.iframes.push({
                          'dialogId': thisDialog.id,
                          'inputName': $(this).prev('input:hidden').attr('name'),
                          'iframe': $iframe,
                          'selector': components[1],
                          'value': this.value });
                      }
                      else
                      {
                        // Update existing <iframe/>
                        thisDialog.iframes[index].value = this.value;
                      }
                    }
                  }

                  // Remove <iframe/> if selecting a pre-existing value
                  else if (undefined !== index && this.value !== thisDialog.iframes[index].iframe.value)
                  {
                    delete thisDialog.iframes[index];
                  }

                  // Store autocomplete display values
                  if (0 < this.value.length)
                  {
                    var dname = $(this).prev('input:hidden').attr('name');
                    dname = dname.substr(0, dname.length - 1) + 'Display]';

                    yuiDialogData[dname] = this.value;
                  }
                }
              });

            this.data[this.id] = yuiDialogData;

            return this;
          }

        this.clear = function ()
          {
            // Remove "id" field
            $('input[name=id]', this.table).remove();

            // Clear fields
            for (fname in this.fields)
            {
              // Radio and checkbox inputs have a length > 0
              if (0 < this.fields[fname].length)
              {
                var initVal = this.initialValues[fname];
                if ('string' === typeof initVal)
                {
                  initVal = [initVal]; // Cast as array
                }

                $(this.fields[fname]).val(initVal);
              }
              else if ('select-one' === this.fields[fname].type)
              {
                // Select first option in single option select controls
                this.fields[fname].options[0].selected = true;
              }
              else
              {
                $(this.fields[fname]).val(this.initialValues[fname]);
              }
            }

            // Clear autocomplete fields
            $('input.form-autocomplete', this.table).val('');

            return this;
          }

        this.remove = function (thisId)
          {
            if (undefined === this.data[thisId])
            {
              return;
            }

            var $tr = $('#' + this.options.displayTable + ' tr[id=' + thisId + ']');

            // Wrap <td/> contents in <div/> so the row hides nicely then hide
            // and remove row
            $tr
              .find('td')
              .wrapInner('<div/>')
              .find('> div')
              .hide('fast', function ()
                {
                  $tr.remove();
                });

            // If this is an existing relationship, then store id for deletion
            if ('new' !== thisId.substr(0, 3))
            {
              this.deletes.push(thisId.match(/\d+$/));
            }

            // Remove data for relation
            delete this.data[thisId];
          }

        this.appendDisplayRow = function ()
          {
            var displayTable = document.getElementById(this.options.displayTable);
            var newRowTemplate = this.options.newRowTemplate;
            if (undefined === displayTable || undefined === newRowTemplate)
            {
              return;
            }

            // Check for special field render handler
            if (undefined !== this.options.handleFieldRender)
            {
              var render = thisDialog.options.handleFieldRender;
            }
            else
            {
              var render = thisDialog.renderField;
            }

            var tr = newRowTemplate.replace('{' + this.fieldPrefix + '[id]}', this.id);
            for (fname in this.fields)
            {
              if (0 < fname.length)
              {
                tr = tr.replace('{' + fname + '}', render.call(this, fname));
              }
            }

            // http://bugs.jquery.com/ticket/7246
            // For unknown reasons, [id=?] selector is not working properly when tr.length > 1
            var rowId = this.id;
            var $row = $(displayTable).find('tbody > tr').filter(function()
              {
                return rowId == this.id;
              });
            if (!$row.length)
            {
              var $tr = $(tr).appendTo(displayTable);
            }
            else
            {
              var $tr = $(tr).replaceAll($row);
            }

            // Bind events
            $tr.click(function ()
              {
                thisDialog.open(this.id);
              });

            $tr.find('button[name=delete]').click(function ()
              {
                thisDialog.remove($(this).closest('tr').attr('id'));
              });
          }

        // Submit dialog
        this.submit = function (yuiDialogData)
          {
            this.save(yuiDialogData);
            this.appendDisplayRow();
            this.clear();
          }

        // Append all cached data to form
        this.appendHiddenFields = function ()
          {
            // Build hidden form input fields
            var i = 0;
            if (null != this.fieldPrefix)
            {
              // Append "s" to old prefix to indicate multiple nature
              var outputPrefix = this.fieldPrefix + 's';
            }
            else
            {
              var outputPrefix = 'dialog';
            }

            for (var id in this.data)
            {
              var thisData = this.data[id];

              // Don't include "id" if it's a "new" object
              if (null != id && 'new' !== id.substr(0,3))
              {
                var name = outputPrefix + '[' + i + '][id]';
                this.$form.append('<input type="hidden" name="' + name + '" value="' + id + '"/>');
              }

              // Convert all event data into hidden input fields
              for (var j in thisData)
              {
                // Format name according to input and output name formats
                var matches = j.match(this.fieldNameFilter);
                if (null != matches)
                {
                  name = outputPrefix + '[' + i + '][' + matches[2] + ']';
                }
                else
                {
                  name = outputPrefix + '[' + i + '][' + name + ']';
                }

                var $hidden = $('<input type="hidden" name="' + name + '" value="' + thisData[j] + '"/>');
                $hidden.appendTo(this.$form);

                // Update this value from iframe
                for (var k in this.iframes)
                {
                  if (id === this.iframes[k].dialogId && j === this.iframes[k].inputName)
                  {
                    this.iframes[k].iframe.one('load', { 'hdn': $hidden }, function (e)
                      {
                        // Update value of hidden input with URI of new resource
                        e.data.hdn.val(this.contentWindow.document.location);

                        // Decrement count of listeners and submit if all done
                        thisDialog.done();
                      });
                  }
                }
              }

              i++;
            }

            // Delete relations that have been removed
            for (var k = 0; k < this.deletes.length; k++)
            {
              this.$form.append('<input type="hidden" name="deleteRelations[' + this.deletes[k] + ']" value="delete"/>');
            }
          }
      }
  })(jQuery);
