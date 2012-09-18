(function ($)
  {
    Qubit.multiFileUpload = Qubit.multiFileUpload || {};

    Drupal.behaviors.multiFileUpload = {
      attach: function (context)
        {
          // Enable uploader
          var uploader = new YAHOO.widget.Uploader('uploaderOverlay');

          // Put swf object over "Select files" button
          var uiLayer = YAHOO.util.Dom.getRegion('selectLink');
          var overlay = $('#uploaderOverlay');
          overlay.width(uiLayer.right - uiLayer.left);
          overlay.height(uiLayer.bottom - uiLayer.top);

          function parseHtmlId(id)
          {
            var yuiId = id.match(/([0-9]+)$/).shift();

            if (null != yuiId && !isNaN(parseInt(yuiId)))
            {
              return yuiId;
            }
          };

          function replacePlaceHolder(templateStr, index)
          {
            var fileName = null;
            index = String(index);

            var matches = templateStr.match(/\%(d+)\%/);

            if (null != matches && 0 < matches[1].length)
            {
              while (matches[1].length > index.length)
              {
                index = '0' + index;
              }

              var fileName = templateStr.replace('%' + matches[1] + '%', index);
            }

            if (null == fileName || templateStr == fileName)
            {
              fileName = templateStr + ' ' + index;
            }

            return fileName;
          }

          // Update all title values for all uploads
          // It is not based on internal yui swfuploader fileID
          function renumerateUploads()
          {
            var title = $('input#title').val();

            $('div.multiFileUploadItem:has(input.md5sum)').each(function (i)
             {
               // Calculate new value
               var newValue = replacePlaceHolder(title, i + 1);

               // Replace title value
               $(this).find('input[type=text]').val(newValue);
             });
          }

          function highlightRepeatedFiles()
          {
            var uploads = $('div.multiFileUploadItem:has(input.md5sum)');
            var memMd5 = Array();

            uploads.each(function()
              {
                var md5sum = $(this).find('input.md5sum').val();

                if (-1 < $.inArray(md5sum, memMd5))
                {
                  var fileName = $('div.multiFileUploadItem:has(input.md5sum[value=' + md5sum + ']):first div.multiFileUploadInfoFilename span.value').text();

                  $(this)
                    .addClass('multiFileUploadWarning')
                    .find('div.messages').remove()
                    .end().prepend('<div class=\"messages status\">' + Qubit.multiFileUpload.i18nDuplicateFile.replace('%1%', '"' + fileName + '"') + '</div>');
                }
                else
                {
                  $(this)
                    .removeClass('multiFileUploadWarning')
                    .find('div.messages').remove();

                  memMd5[memMd5.length] = md5sum;
                }
              });
          }

          $('input#title').live('keyup', renumerateUploads);

          $('a.uploadActionRetry, a.uploadActionStart').live('click', function()
            {
              var fileId = parseHtmlId($(this).closest('.multiFileUploadItem').attr('id'));

              // Upload it
              uploader.upload('file' + fileId, Qubit.multiFileUpload.uploadResponsePath, 'POST', { informationObjectId: Qubit.multiFileUpload.informationObjectId });

              return false;
            });

          $('a.uploadActionDelete').live('click', function()
            {
              var fileId = parseHtmlId($(this).closest('.multiFileUploadItem').attr('id'));

              // Hide block
              $('div#upload-file' + fileId).slideUp('normal', function()
                {
                  // Remove it
                  $(this).remove();

                  renumerateUploads();
                  highlightRepeatedFiles();
                });

              return false;
            });

          $('a.uploadActionCancel').live('click', function()
            {
              var fileId = parseHtmlId($(this).closest('.multiFileUploadItem').attr('id'));
              var uploadLayer = $('div#upload-file' + fileId);

              // Hide block
              uploadLayer.slideUp('normal', function()
              {
                // Remove it
                $(this).remove();

                // Cancel upload
                uploader.cancel('file' + fileId);

                // Remove file from the queue
                uploader.removeFile('file' + fileId);
              });

              return false;
            });

          uploader.addListener('contentReady', function ()
            {
              // Allows multiple file selection in "Browse" dialog.
              uploader.setAllowMultipleFiles(true);
              uploader.setAllowLogging(true);
            });

          uploader.addListener('fileSelect', function (event)
            {
              if ('fileList' in event && event.fileList != null)
              {
                // Make space for a thumbnail and progress bar
                for (var i in event.fileList)
                {
                  var file = event.fileList[i];

                  // Create an upload block for each upload
                  var uploadItem = $('<div id="upload-' + file.id + '" class="multiFileUploadItem"></div>')
                    // Insert element in uploads layer
                    .appendTo('#uploads');

                  if (-1 < Qubit.multiFileUpload.maxUploadSize && file.size > Qubit.multiFileUpload.maxUploadSize)
                  {
                    uploadItem
                      // Add warning class
                      .addClass('multiFileUploadWarning')

                      // Set error message
                      .html('<p><strong>' + file.name + '</strong><br/>' + Qubit.multiFileUpload.i18nOversizedFile + '</p>');

                    // Remove file from uploader queue
                    uploader.removeFile(file.id);
                  }
                  else
                  {
                    // Render thumbnail box and progress bar
                    $(uploadItem)
                      .html('<div id="thumbnail-' + file.id + '" class="multiFileUploadThumbItem" style="width: ' + Qubit.multiFileUpload.thumbWidth + 'px">' +
                              '<div class="uploadStatus"><span>' + Qubit.multiFileUpload.i18nWaiting + '</span></div>' +
                              '<div class="progressBar" style="width: ' + Qubit.multiFileUpload.thumbWidth + 'px;">' +
                                '<div style="height: 5px; width:' + Qubit.multiFileUpload.thumbWidth + 'px; background-color: #CCC;"></div>' +
                              '</div>' +
                            '</div>' +
                            '<div class="multiFileUploadInfo">' +
                              '<div class="multiFileUploadInfoFilename">' +
                                '<span class="title">' + Qubit.multiFileUpload.i18nFilename + ':</span>' +
                                '<span class="value">' + file.name + '</span>' +
                              '</div>' +
                              '<div class="multiFileUploadInfoFilesize">' +
                                '<span class="title">' + Qubit.multiFileUpload.i18nFilesize + ':</span>' +
                                '<span class="value">' + file.size + ' bytes</span>' +
                              '</div>' +
                              '<div class="multiFileUploadInfoActions">' +
                                '<a href="#" class="uploadActionStart">' + Qubit.multiFileUpload.i18nStart + '</a>' +
                                '<a href="#" class="uploadActionCancel" style="display: none;">' + Qubit.multiFileUpload.i18nCancel + '</a>' +
                                '<a href="#" class="uploadActionDelete" style="display: none;">' + Qubit.multiFileUpload.i18nDelete + '</a>' +
                              '</div>' +
                            '</div>');
                  }
                }

                // Preventing simultaneous uploads
                uploader.setSimUploadLimit(1);

                // Start upload!
                uploader.uploadAll(Qubit.multiFileUpload.uploadResponsePath, 'POST', { informationObjectId: Qubit.multiFileUpload.informationObjectId });
              }
            });

          uploader.addListener('uploadStart', function(event)
            {
              var uploadLayer = $('#upload-' + event.id);

              $('div.uploadStatus', uploadLayer)
                .html('<span>' + Qubit.multiFileUpload.i18nUploading + '</span>');

              // Show cancel button
              $('a.uploadActionCancel', uploadLayer).show();

              // Hide start button
              $('a.uploadActionStart', uploadLayer).hide();
            });

          uploader.addListener('uploadProgress', function (event)
            {
              var thumbnailLayer = $('#thumbnail-' + event.id);
              var statusLayer = $('div.uploadStatus', thumbnailLayer);

              var progress = Math.round(Qubit.multiFileUpload.thumbWidth * (event.bytesLoaded / event.bytesTotal));
              var progressBar = '<div style="background-color: #fd3; height: 5px; width: ' + progress + 'px"/>';

              // Update progress bar
              $('div.progressBar', thumbnailLayer).html(progressBar);

              // Update status message
              if (event.bytesLoaded != event.bytesTotal)
              {
                $('span', statusLayer).text(Qubit.multiFileUpload.i18nUploading + ' ' + Math.round(event.bytesLoaded / event.bytesTotal * 100) + '%');
              }
              else
              {
                $('span', statusLayer).text(Qubit.multiFileUpload.i18nLoadingPreview);
              }
            });

          uploader.addListener('uploadComplete', function (event)
            {
              var thumbnailLayer = $('#thumbnail-' + event.id);
              var infoLayer = thumbnailLayer.next();

              var progressBar = '<div style="background-color: #0f0; height: 5px; width: ' + Qubit.multiFileUpload.thumbWidth + 'px"/>';

              // Update progress bar
              $('div.progressBar', thumbnailLayer).html(progressBar);

              // Remove cancel button
              $('a.uploadActionCancel', infoLayer).hide();

              // Show delete button
              $('a.uploadActionDelete', infoLayer).show();
            });

          uploader.addListener('uploadCompleteData', function (event)
            {
              // Parse server response for each upload
              var upload = $.parseJSON(event.data);

              // Remove this file from the upload queue
              uploader.removeFile(event.id);

              // Layers for current upload
              var thumbnailLayer = $('#thumbnail-' + event.id);

              if ('error' in upload)
              {
                var uploadDiv = $('#upload-' + event.id);

                // Add error message
                uploadDiv.prepend('<div class="error">' + upload.error + '</div>');

                // Remove thumbnail box
                thumbnailLayer.remove();

                return;
              }

              thumbnailLayer
                // Render img tag
                .html('<img src="' + Qubit.multiFileUpload.uploadTmpDir + '/' + upload.thumb + '"/>')

                // Give thumbnail div a minimum height to prevent text from wrapping to next line
                .attr('style', function(i) {
                  return $(this).attr('style') + '; min-length; 100px;'; });

              // Get the file index from the id passed by YUI
              var fileId = parseHtmlId(event.id);

              // Render final upload
              $('<div class="form-item">' +
                  '<label>' + Qubit.multiFileUpload.i18nInfoObjectTitle + '</label>' +
                  '<input type="text" name="files[' + fileId + '][infoObjectTitle]" value="" style="width: 250px"/>' +
                  '<input type="hidden" class="md5sum" value="' + upload.md5sum + '"/>' +
                  '<input type="hidden" class="filename" value="' + upload.name + '"/>' +
                  '<input type="hidden" name="files[' + fileId + '][name]" value="' + upload.name + '"/>' +
                  '<input type="hidden" name="files[' + fileId + '][md5sum]" value="' + upload.md5sum + '"/>' +
                  '<input type="hidden" name="files[' + fileId + '][tmpName]" value="' + upload.tmpName + '"/>' +
                  '<input type="hidden" name="files[' + fileId + '][thumb]" value="' + upload.thumb + '"/>' +
                '</div>')
                .prependTo(thumbnailLayer.next());

              renumerateUploads();
              highlightRepeatedFiles();
            });

          uploader.addListener('uploadError', function(event)
            {
              log(event);

              var thumbnailLayer = $('#thumbnail-' + event.id);

              // Add error message to progress bar
              $('div.uploadStatus', thumbnailLayer)
                .html('<a href="#" class="uploadActionRetry">' + Qubit.multiFileUpload.i18nUploadError + '</a>')

              // Remove cancel button
              thumbnailLayer.find('a.uploadActionCancel').remove();
            });

          uploader.addListener('rollOver', function ()
            {
              $('#selectLink').addClass('hover');
            });

          uploader.addListener('rollOut', function ()
            {
              $('#selectLink').removeClass('hover');
            });

          // uploader.addListener('cancel', function () { });
          // uploader.addListener('click', function () { });
          // uploader.addListener('mouseDown', function () { });
          // uploader.addListener('mouseUp', function () { });
        }
    }
  })(jQuery);

