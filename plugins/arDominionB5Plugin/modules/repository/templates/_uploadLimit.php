<section class="card mb-3" id="upload-limit-card">

  <div class="card-body alert-success d-none" role="alert" aria-hidden="true">
    <?php echo __('Update successful'); ?>
  </div>

  <div class="card-body alert-danger d-none" role="alert" aria-hidden="true">
    <?php echo __('Update failed'); ?>
  </div>

  <h5 class="p-3 mb-1 border-bottom">
    <?php echo __('Upload limit'); ?>
    <?php if ('sfIsdiahPlugin' != $sf_context->getModuleName()) { ?>
      <span class="d-block text-muted small mt-1">
        <?php echo __('for %repo%', ['%repo%' => link_to($resource->__toString(), [$resource, 'module' => 'repository'])]); ?>
      </span>
    <?php } ?>
  </h5>

  <div class="card-body py-2">
    <?php if ('limited' == $quotaType) { ?>
      <div class="progress mb-1" style="height: 25px;">
        <div
          class="progress-bar"
          style="width: <?php echo $sf_data->getRaw('diskUsageFloat'); ?>%;"
          role="progressbar"
          aria-valuenow="<?php echo $sf_data->getRaw('diskUsageFloat'); ?>"
          aria-valuemin="0"
          aria-valuemax="100">
        </div>
      </div>
      <p class="card-text"><?php echo __('%du% of %limit% <abbr title="1 GB = 1 000 000 000 bytes">GB</abbr> (%percent%%)', ['%du%' => $sf_data->getRaw('diskUsage'), '%limit%' => $sf_data->getRaw('uploadLimit'), '%percent%' => $sf_data->getRaw('diskUsagePercent')]); ?></p>
    <?php } elseif ('disabled' == $quotaType) { ?>
      <p class="card-text"><?php echo __('Upload is disabled'); ?></p>
    <?php } elseif ('unlimited' == $quotaType) { ?>
      <p class="card-text"><?php echo __('%du% <abbr title="1 GB = 1 000 000 000 bytes">GB</abbr> of <em>Unlimited</em>&nbsp;', ['%du%' => $sf_data->getRaw('diskUsage')]); ?></p>
    <?php } ?>
  </div>

  <div class="card-body">
    <?php if ($sf_user->isAdministrator()) { ?>
      <a href="#" class="btn atom-btn-white" data-bs-toggle="modal" data-bs-target="#upload-limit-modal"><?php echo __('Edit'); ?></a>
    <?php } ?>
  </div>

</section>

<?php if ($sf_user->isAdministrator() && !$noedit) { ?>
  <div
  class="modal fade"
  id="upload-limit-modal"
  data-bs-backdrop="static"
  tabindex="-1"
  aria-labelledby="upload-limit-modal-heading"
  aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="upload-limit-modal-heading">
            <?php echo __('Edit upload limit'); ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal">
            <span class="visually-hidden"><?php echo __('Close'); ?></span>
          </button>
        </div>
        <div class="modal-body">

          <form id="upload-limit-form" method="POST" action="<?php echo url_for([$resource, 'module' => 'repository', 'action' => 'editUploadLimit']); ?>">
            <?php echo $form->renderHiddenFields(); ?>
            <div>
              <label for="uploadLimit_type"><?php echo __('Set the upload limit for this %1%', ['%1%' => strtolower(sfConfig::get('app_ui_label_repository'))]); ?></label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="uploadLimit[type]" id="uploadLimit_type_disabled" value="disabled"<?php echo ('disabled' == $quotaType) ? ' checked' : ''; ?>>
                <label class="form-check-label" for="uploadLimit_type_disabled">
                  <?php echo __('Disable uploads'); ?>
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="uploadLimit[type]" id="uploadLimit_type_limited" value="limited"<?php echo ('limited' == $quotaType) ? ' checked' : ''; ?>>
                <label class="form-check-label" for="uploadLimit_type_limited">
                  <?php echo __('Limit uploads to %1% GB', ['%1%' => '<input class="form-control form-control-sm d-inline" id="uploadLimit_value" type="number" step="any" name="uploadLimit[value]" value="'.(($resource->uploadLimit > 0) ? $resource->uploadLimit : '').'" style="width: 6em" />']); ?>
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="uploadLimit[type]" id="uploadLimit_type_unlimited" value="unlimited"<?php echo ('unlimited' == $quotaType) ? ' checked' : ''; ?>>
                <label class="form-check-label" for="uploadLimit_type_unlimited">
                  <?php echo __('Allow unlimited uploads', ['%1%' => sfConfig::get('app_ui_label_repository')]); ?>
                </label>
              </div>
            </div>
          </form>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <?php echo __('Cancel'); ?>
          </button>
          <button type="button" class="btn btn-success">
            <?php echo __('Save'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
