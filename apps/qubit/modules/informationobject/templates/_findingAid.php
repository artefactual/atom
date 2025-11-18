<h4 class="h5 mb-2"><?php echo __('Finding aid'); ?></h4>
<ul class="list-unstyled">

  <?php if ($showStatus) { ?>
    <li>
      <i class="fas fa-fw fa-info-circle me-1 text-muted" aria-hidden="true">
      </i><?php echo __('Status: %1', ['%1' => $status]); ?>
    </li>
  <?php } ?>

  <?php if ($showGenerate) { ?>
    <li>
      <a
        id="generate-finding-aid"
        class="atom-icon-link"
        href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'generateFindingAid']); ?>"
        data-cy="generate-finding-aid"
      >
        <i class="fas fa-fw fa-cogs me-1" aria-hidden="true">
        </i><?php echo __('Generate'); ?>
      </a>
    </li>
  <?php } ?>

  <?php if ($showUpload) { ?>
    <li>
      <a
        id="upload-finding-aid"
        class="atom-icon-link"
        href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'uploadFindingAid']); ?>"
        data-cy="upload-finding-aid"
      >
        <i class="fas fa-fw fa-upload me-1" aria-hidden="true">
        </i><?php echo __('Upload'); ?>
      </a>
    </li>
  <?php } ?>

  <?php if ($showDelete) { ?>
    <li>
      <a class="atom-icon-link" href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'deleteFindingAid']); ?>">
        <i class="fas fa-fw fa-times me-1" aria-hidden="true">
        </i><?php echo __('Delete'); ?>
      </a>
    </li>
  <?php } ?>

  <?php if ($showDownload) { ?>
    <li>
      <a class="btn atom-btn-white" href="<?php echo public_path($path); ?>" target="_blank">
        <i class="fas fa-lg fa-file-pdf" aria-hidden="true"></i>
        <span class="ms-2"><?php echo __('Download'); ?></span>
      </a>
    </li>
  <?php } ?>

</ul>
