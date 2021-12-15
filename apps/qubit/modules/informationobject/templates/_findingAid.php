<li class="separator"><h4><?php echo __('Finding aid'); ?></h4></li>

<?php if ($showStatus) { ?>
  <li>
    <a>
      <i class="fa fa-info-circle"></i>
      <?php echo __('Status: %1', ['%1' => $status]); ?>
    </a>
  </li>
<?php } ?>

<?php if ($showGenerate) { ?>
  <li>
    <a
      id="generate-finding-aid"
      href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'generateFindingAid']); ?>"
      data-cy="generate-finding-aid"
    >
      <i class="fa fa-cogs"></i>
      <?php echo __('Generate'); ?>
    </a>
  </li>
<?php } ?>

<?php if ($showUpload) { ?>
  <li>
    <a
      id="upload-finding-aid"
      href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'uploadFindingAid']); ?>"
      data-cy="upload-finding-aid"
    >
      <i class="fa fa-upload"></i>
      <?php echo __('Upload'); ?>
    </a>
  </li>
<?php } ?>

<?php if ($showDelete) { ?>
  <li>
    <a href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'deleteFindingAid']); ?>">
      <i class="fa fa-times"></i>
      <?php echo __('Delete'); ?>
    </a>
  </li>
<?php } ?>

<?php if ($showDownload) { ?>
  <li>
    <a class="btn" href="<?php echo public_path($path); ?>" target="_blank">
      <i class="fa fa-file-pdf-o fa-lg"></i>
      <?php echo __('Download'); ?>
    </a>
  </li>
<?php } ?>
