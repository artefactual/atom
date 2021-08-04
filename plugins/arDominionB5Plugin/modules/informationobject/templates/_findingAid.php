<h4 class="h5 mb-2"><?php echo __('Finding aid'); ?></h4>
<ul class="list-unstyled">

  <?php if ($showStatus) { ?>
    <li>
      <a>
        <i class="fas fa-fw fa-info-circle text-muted" aria-hidden="true"></i>
        <?php echo __('Status: %1', ['%1' => $status]); ?>
      </a>
    </li>
  <?php } ?>

  <?php if ($showGenerate) { ?>
    <li>
      <a href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'generateFindingAid']); ?>">
        <i class="fas fa-fw fa-cogs text-muted" aria-hidden="true"></i>
        <?php echo __('Generate'); ?>
      </a>
    </li>
  <?php } ?>

  <?php if ($showUpload) { ?>
    <li>
      <a href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'uploadFindingAid']); ?>">
        <i class="fas fa-fw fa-upload text-muted" aria-hidden="true"></i>
        <?php echo __('Upload'); ?>
      </a>
    </li>
  <?php } ?>

  <?php if ($showDelete) { ?>
    <li>
      <a href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'deleteFindingAid']); ?>">
        <i class="fas fa-fw fa-times text-muted" aria-hidden="true"></i>
        <?php echo __('Delete'); ?>
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
