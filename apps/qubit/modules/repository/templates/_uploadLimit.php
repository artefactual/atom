<!-- upload limit display with usage bar -->
<section id="uploadLimitDisplay" class="sidebar-widget">

  <h3><?php echo __('Upload limit') ?></h3>

  <div>

    <?php if ('sfIsdiahPlugin' != $sf_context->getModuleName()): ?>
      <?php echo __('for %repo%', array('%repo%' => link_to($resource->__toString(), array($resource, 'module' => 'repository')))) ?>
    <?php endif; ?>

    <?php if ('limited' == $quotaType): ?>
      <div class="usageBar">
        <div style="width: <?php echo $usageBarPixels ?>px; background-color: <?php echo $usageBarColor ?>;"></div>
      </div>
      <?php echo __('%du% of %limit% <abbr title="1 GB = 1 000 000 000 bytes">GB</abbr> (%percent%%)', array('%du%' => $sf_data->getRaw('diskUsage'), '%limit%' => $sf_data->getRaw('uploadLimit'), '%percent%' => $sf_data->getRaw('diskUsagePercent'))) ?>
    <?php elseif ('disabled' == $quotaType): ?>
      <?php echo __('Upload is disabled') ?>
    <?php elseif ('unlimited' == $quotaType): ?>
      <?php echo __('%du% <abbr title="1 GB = 1 000 000 000 bytes">GB</abbr> of <em>Unlimited</em>&nbsp;', array('%du%' => $sf_data->getRaw('diskUsage'))) ?>
    <?php endif; ?>

    <?php if ($sf_user->isAdministrator()): ?>
      (<a href="#" id="editUlLink"><?php echo __('Edit') ?></a>)
    <?php endif; ?>

  </div>

</section>

<?php if ($sf_user->isAdministrator() && !$noedit): ?>

<!-- Edit upload limit -->
<!-- Note: YUI dialog hides this entire div -->
<div class="section" id="editUploadLimit">
  <div class="hd"><?php echo __('Edit upload limit') ?></div>

  <div class="bd form-item-uploadLimit">

    <form id="uploadLimitForm" method="POST" action="<?php echo url_for(array($resource, 'module' => 'repository', 'action' => 'editUploadLimit')) ?>">

      <div class="form-item">

        <label for="uploadLimit_type"><?php echo __('Set the upload limit for this %1%', array('%1%' => strtolower(sfConfig::get('app_ui_label_repository')))) ?></label>

        <ul class="radio_list">
          <li class="radio">
            <input id="uploadLimit_type_disabled" type="radio" name="uploadLimit[type]" value="disabled"<?php echo ('disabled' == $quotaType) ? ' checked' : '' ?> />
            <label class="radio" for="uploadLimit_type_disabled"><?php echo __('Disable uploads') ?></label>
          </li>
          <li class="radio">
            <input id="uploadLimit_type_limited" type="radio" name="uploadLimit[type]" value="limited"<?php echo ('limited' == $quotaType) ? ' checked' : '' ?> />
            <label for="uploadLimit_type_limited"><?php echo __('Limit uploads to %1% GB', array('%1%' => '<input id="uploadLimit_value" type="text" name="uploadLimit[value]" value="'.(($resource->uploadLimit > 0) ? $resource->uploadLimit : '').'" style="width: 6em" />')) ?></label>
          </li>
          <li class="radio">
            <input id="uploadLimit_type_unlimited" type="radio" name="uploadLimit[type]" value="unlimited"<?php echo ('unlimited' == $quotaType) ? ' checked' : '' ?> />
            <label for="uploadLimit_type_unlimited"><?php echo __('Allow unlimited uploads', array('%1%' => sfConfig::get('app_ui_label_repository'))) ?></label>
          </li>
        </ul>

      </div>

    </form>
  </div>

</div>

<script type="text/javascript">
//<![CDATA[
Drupal.behaviors.uploadLimitDialog = {
  attach: function (context)
    {
      (function ($)
        {
          var messageSuccess = '<?php echo __('Update successful') ?>';
          var submitButtonText = '<?php echo __('Save') ?>';
          var cancelButtonText = '<?php echo __('Cancel') ?>';

          // Define various event handlers for Dialog
          var handleSubmit = function() {
            this.submit();
          };
          var handleCancel = function() {
            this.cancel();
          };
          var handleSuccess = function(o) {
            var response = o.responseText;

            // Replace previous HTML with response from editUploadLimit
            $('#uploadLimitDisplay').replaceWith(response);

            // Flash message confirming update
            var $notice = $('<div class="alert-message block-message success">'+messageSuccess+'</div>').hide();
            $notice.insertBefore("#uploadLimitDisplay > div").show(500, function ()
              {
                setTimeout(function ()
                {
                  $notice.hide(500, function()
                    {
                      $notice.remove();
                    })
                }, 2000)
              });

            // Rebind edit link
            var ulDialog = this;
            $('#editUlLink').click(function ()
            {
              ulDialog.show();
            });
          };
          var handleFailure = function(o) {
            // do something?
          };

          // Instantiate the Dialog
          var ulDialog = new YAHOO.widget.Dialog("editUploadLimit",
            { fixedcenter : true,
              visible : false,
              modal: true,
              constraintoviewport : true,
              zIndex : 20000,
              buttons : [ { text:submitButtonText, handler:handleSubmit, isDefault:true },
                          { text:cancelButtonText, handler:handleCancel } ]
            });

          // Wire up the success and failure handlers
          ulDialog.callback = {
            success: handleSuccess,
            failure: handleFailure,
            scope: ulDialog
          };

          // Render the Dialog
          ulDialog.render();

          // Show dialog on click of "edit" link
          $('#editUlLink').click(function ()
          {
            ulDialog.show();
          });

          // Focus on text box when radio is clicked for a quicker update
          $('#uploadLimit_type_limited, label[for=uploadLimit_type_limited]').click(function(event)
            {
              $('input#uploadLimit_value').focus().select();
            });

        })(jQuery)
    }
}

//]]>
</script>
<?php endif; // Edit dialog for admins only ?>
