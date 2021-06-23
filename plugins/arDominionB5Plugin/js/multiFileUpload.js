(function ($) {

  "use strict";

  function MultiFileUpload (element)
  {
    this.$element = $(element).find('div.multiFileUpload');

    this.uppy = new Uppy.Core({ 
      debug: false,
      id: 'uppy-atom',
      autoProceed: false,
      restrictions: {
        minNumberOfFiles: 1
      },
      onBeforeFileAdded: (currentFile, files) => this.onBeforeFileAddedChecks(currentFile, files),
    });

    this.nextImageNum = 1;
    this.uploadItems = [];
    this.result = "";

    this.$submitButton = $(element).find('input[type="submit"]');
    this.$cancelButton = $(element).find('a[title="Cancel"]');
    this.$retryButton = $('<a class="c-btn" title="retry"/>')
      .attr('type','hidden')
      .text(this.$element.data('multifileupload-i18nretry'))
      .appendTo('.actions');

    this.init();
    this.listen();
  };


  MultiFileUpload.prototype = {

    init: function()
    {
      this.$retryButton.hide();

      let noteText = this.$element.data('multifileupload-i18nmaxsizenote')
          .replace('%{maxFileSizeMessage}', this.$element.data('multifileupload-i18nmaxfilesizemessage') + this.$element.data('multifileupload-maxfilesize') / 1024 / 1024 + "MB")
          .replace('%{maxPostSizeMessage}', this.$element.data('multifileupload-i18nmaxpostsizemessage') + this.$element.data('multifileupload-maxpostsize') / 1024 / 1024 + "MB");

      this.uppy
        .use(Uppy.Dashboard, {
          id: 'dashboard-atom',
          inline: true,
          target: '.uppy-dashboard',
          width: '100%',
          height: '400px',
          hideUploadButton: true,
          replaceTargetContent: true,
          showProgressDetails: true,
          hideCancelButton: true,
          hideAfterFinish: true,
          hideRetryButton: true,
          note: noteText,
          doneButtonHandler: null,
          browserBackButtonClose: false,
          fileManagerSelectionType: 'files',
          proudlyDisplayPoweredByUppy: false,
          closeModalOnClickOutside: false,
          hideDoneButton: true,
          locale: {
            strings: {
              done: this.$element.data('multifileupload-i18nsave'),
              // 'Add more' hover text.
              addMoreFiles: this.$element.data('multifileupload-i18naddmorefiles'),
              // 'Add more' button label.
              addMore: this.$element.data('multifileupload-i18naddmore'),
              addingMoreFiles: this.$element.data('multifileupload-i18naddingmorefiles'),
              xFilesSelected: {
                0: this.$element.data('multifileupload-i18nfileselected'),
                1: this.$element.data('multifileupload-i18nfilesselected')
              },
              // Upload status strings.
              uploading: this.$element.data('multifileupload-i18nuploading'),
              complete: this.$element.data('multifileupload-i18ncomplete'),
              uploadFailed: this.$element.data('multifileupload-i18nuploadfailed'),
              // Remove file hover text.
              removeFile: this.$element.data('multifileupload-i18nremovefile'),
              // Main 'drop here' message.
              dropPaste: this.$element.data('multifileupload-i18ndropfile'),
              filesUploadedOfTotal: {
                0: this.$element.data('multifileupload-i18nfileuploadedoftotal'),
                1: this.$element.data('multifileupload-i18nfilesuploadedoftotal')
              },
              dataUploadedOfTotal: this.$element.data('multifileupload-i18ndatauploadedoftotal'),
              // When `showProgressDetails` is set, shows an estimation of how long the upload will take to complete.
              xTimeLeft: this.$element.data('multifileupload-i18ntimeleft'),
              uploadingXFiles: {
                0: this.$element.data('multifileupload-i18nuploadingfile'),
                1: this.$element.data('multifileupload-i18nuploadingfiles')
              },
              // Label cancel button.
              cancel: this.$element.data('multifileupload-i18ncancel'),
              // Edit file hover text.
              edit: this.$element.data('multifileupload-i18nedit'),
              // Save changes button.
              saveChanges: this.$element.data('multifileupload-i18nsave'),
              // Leave 'Add more' dialog.
              back: this.$element.data('multifileupload-i18nback'),
              // Edit Title dialog message.
              editing: this.$element.data('multifileupload-i18nediting'),
              failedToUpload: this.$element.data('multifileupload-i18nfailedtoupload'),
            }
          },
          thumbnailWidth: this.$element.data('multifileupload-thumbwidth'),
          trigger: '#pick-files',
          // Enable editing of field with id 'title' label: 'Title'
          metaFields: [
            { id: 'title', name: this.$element.data('multifileupload-i18ninfoobjecttitle') },
          ],
        })
        .use(Uppy.XHRUpload, {
          endpoint: this.$element.data('multifileupload-uploadresponsepath'),
          formData: true,
          method: 'post',
          limit: 10,
          fieldName: 'Filedata',
          parentSlug: this.$element.data('multifileupload-slug'),
        })
        .on('upload-success', $.proxy(this.onUploadSuccess, this))
        .on('complete', $.proxy(this.onComplete, this))
        .on('file-added', $.proxy(this.onFileAdded, this))
        .on('cancel-all', $.proxy(this.onCancelAll, this));
    },

    listen: function ()
    {
      // Intercept AtoM's Submit button.
      this.$submitButton.on('click', $.proxy(this.onSubmitButton, this));
      this.$retryButton.on('click', $.proxy(this.onRetryButton, this));
    },

    // Retry is available if some/all DO's do not successfully upload.
    onRetryButton: function ()
    {
      this.uppy.retryAll().then((result) => {
        if (this.uppy.getState().error === null && result.successful.length > 0 && result.failed.length === 0) {
          this.$retryButton.hide();
          this.showAlert(this.$element.data('multifileupload-i18nretrysuccess'), 'alert-info');
        }
      })
    },

    // Checks if ANY uploads were successful.
    checkUploadSuccessful: function ()
    {
      const uploaded = (element) => element.progress.uploadComplete === true;
      var completed = this.uppy.getFiles().some(uploaded);

      return completed;
    },

    // Import button logic.
    onSubmitButton: function ()
    {
      this.clearAlerts();

      // Ensure they are not on Uppy's 'add more' page. Do not allow uppy.upload() to
      // be called while 'add more' is open.
      if ($(".uppy-DashboardContent-back").length) {
        $(".uppy-DashboardContent-back").click();
      }

      // Ensure that some files have been added for upload.
      if (this.uppy.getFiles().length == 0) {
        this.showAlert(this.$element.data('multifileupload-i18nnofileserror'), 'alert-info');

        return false;
      }

      if (this.uppy.getState().error) {
        if (this.checkUploadSuccessful() === true) {
          this.$submitButton.attr('disabled', 'disabled');
          this.$cancelButton.removeAttr("href").attr('disabled', 'disabled');
          this.showAlert(this.$element.data('multifileupload-i18nimporting'), 'alert-info');
          // Post any successful uploads.
          $('#multiFileUploadForm').submit();
        }
        else {
          // In error state with zero successful uploads. Prevent POST.
          this.showAlert(this.$element.data('multifileupload-i18nnosuccessfulfileserror'), 'alert-error');

          return false;
        }
      }
      else {
        // Upload to AtoM - wait on promise until all complete.
        this.uppy.upload().then((result) => {
          if (result.failed.length > 0) {
            (this.checkUploadSuccessful() === true) ?
              this.showAlert(this.$element.data('multifileupload-i18nsomefilesfailederror'), 'alert-error') :
              this.showAlert(this.$element.data('multifileupload-i18nnosuccessfulfileserror'), 'alert-error');

            this.$retryButton.show();
          }
          else {
            this.$submitButton.attr('disabled', 'disabled');
            this.$cancelButton.removeAttr("href").attr('disabled', 'disabled');
            this.showAlert(this.$element.data('multifileupload-i18nimporting'), 'alert-info');
            // Post to multiFileUpload.
            $('#multiFileUploadForm').submit();
          }
        })
      }

      return false;
    },

    // Push a record of successful file upload into array uploadItems. 
    // These will be added to this array in order of when they completed uploading.
    // This info is needed to build the hidden form elements once all files 
    // have completed uploading to AtoM.
    onUploadSuccess: function (file, response)
    {
      this.uploadItems.push({file, response});
    },

    // onComplete runs when all uploads are complete - even if there were errors.
    // Adds the form elements in the same order as result.successful so that
    // they are imported into AtoM: Image 01, Image 02, etc.
    onComplete: function (result)
    {
      // Iterates over successfully uploaded items.
      var uploadItems = this.uploadItems;

      $.each(result.successful, function(key, file) {
        // Get the corresponding upload response.
        var fileResponse = uploadItems.find(x => x.file.id === file.id).response;

        // Add hidden form elements for each successfully uploaded file.
        $('<div class="multiFileUploadItem" id=' + file.id + '>' +
          '<div class="multiFileUploadInfo">' +
            '<div class="form-item">' +
              '<input type="hidden" class="filename" value="' + fileResponse.body.name + '"/>' +
              '<input type="hidden" class="md5sum" value="' + fileResponse.body.md5sum + '"/>' +
              '<input type="hidden" name="files[' + file.id + '][name]" value="' + fileResponse.body.name + '"/>' +
              '<input type="hidden" name="files[' + file.id + '][md5sum]" value="' + fileResponse.body.md5sum + '"/>' +
              '<input type="hidden" name="files[' + file.id + '][tmpName]" value="' + fileResponse.body.tmpName + '"/>' +
              '<input type="hidden" class="title" name="files[' + file.id + '][infoObjectTitle]" value="' + file.meta.title + '"/>' +
            '</div>' +
          '</div>' +
        '</div>')
        .appendTo("#uploads");
      });
    },

    onBeforeFileAddedChecks: function (currentFile, files)
    {
      // Ensure currentFile is not larger that AtoM's max file upload size.
      if (currentFile.data.size > this.$element.data('multifileupload-maxfilesize')) {
        let fileName = currentFile.data.name;
        let maxSize = this.$element.data('multifileupload-maxfilesize') / 1024 / 1024;
        let fileSize = (currentFile.data.size / 1024 / 1024).toFixed(2);
        let sizeErrorText = this.$element.data('multifileupload-i18nsizeerror')
          .replace('%{fileName}', fileName)
          .replace('%{fileSize}', fileSize)
          .replace('%{maxSize}', maxSize);

        // Add console mssg and alert error.
        this.uppy.log(sizeErrorText);
        this.showAlert(sizeErrorText, 'alert-info');

        // Press the Uppy back button after the error to return to the Dashboard.
        if ($(".uppy-DashboardContent-back").length) {
          $(".uppy-DashboardContent-back").click();
        }

        return false;
      }

      // Watch total size of upload and ensure it's not larger than AtoM's POST size config.
      if ((this.getTotalFileSize(files) + currentFile.data.size) > this.$element.data('multifileupload-maxpostsize')) {
        let maxPostSize = this.$element.data('multifileupload-maxpostsize') / 1024 / 1024;
        let postSizeErrorText = this.$element.data('multifileupload-i18npostsizeerror')
          .replace('%{maxPostSize}', maxPostSize);

        this.clearAlerts();

        // Add console mssg and alert error.
        this.uppy.log(postSizeErrorText);
        this.showAlert(postSizeErrorText, 'alert-info');

        // Press the Uppy back button after the error to return to the Dashboard.
        if ($(".uppy-DashboardContent-back").length) {
          $(".uppy-DashboardContent-back").click();
        }

        return false;
      }
    },

    getTotalFileSize: function (files)
    {
      let totalFileSize = 0;

      if (!files) {
        files = this.uppy.getFiles();
      }

      for (var key in files) {
        totalFileSize = totalFileSize + files[key].size;
      }

      return totalFileSize;
    },

    // Set parentSlug and template-based title when files are added to the Dashboard.
    onFileAdded: function (file) 
    {
      this.uppy.setFileMeta(file.id, {
        parentSlug: this.$element.data('multifileupload-slug'),
        title: this.replacePlaceHolder($('input#title').val(), this.nextImageNum++)
      });
    },

    reset: function () 
    {
      this.uploadItems = [];
      this.nextImageNum = 1;
    },

    // User pressed cancel - reset upload state.
    onCancelAll: function ()
    {
      // Delete all file upload hidden form items.
      uploads = document.getElementById("uploads");
        while (uploads.firstChild) {
          uploads.removeChild(uploads.lastChild);
        }

      // Reset internal vars.
      this.reset();
    },

    // Show an AtoM style alert message.
    showAlert: function (message, type) {
      if (!type) {
        type = 'alert-info';
      }

      var $alert = $('<div class="alert ' + type + ' animateNicely">');
      $alert.append('<button type="button" data-dismiss="alert" class="close">&times;</button>');
      $alert.append(message).prependTo($('#uploaderContainer'));
  
      return $alert;
    },

    clearAlerts: function () {
      $("div#uploaderContainer > div").remove( ".alert" );
    },

    // Build title from Title field template.
    replacePlaceHolder: function (templateStr, index) 
    {
      var fileName = null;
      index = String(index);
      var matches = templateStr.match(/\%(d+)\%/);

      if (null != matches && 0 < matches[1].length) {
        while (matches[1].length > index.length) {
          index = '0' + index;
        }

        var fileName = templateStr.replace('%' + matches[1] + '%', index);
      }

      if (null == fileName || templateStr == fileName) {
        fileName = templateStr + ' ' + index;
      }

      return fileName;
    }
  };

  $(function()
  {
    var $node = $('.multiFileUpload');

    if ($node.length)
    {
      new MultiFileUpload($node.get(0));
    }
  });

})(jQuery);
