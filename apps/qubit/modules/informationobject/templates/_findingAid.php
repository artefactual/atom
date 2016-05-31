<li class="separator"><h4><?php echo __('Finding aid') ?></h4></li>

<?php if ($showStatus): ?>
  <li>
    <a>
      <i class="fa fa-info-circle"></i>
      <?php echo __('Status: %1', array('%1' => $status)) ?>
    </a>
  </li>
<?php endif; ?>

<?php if ($showGenerate): ?>
  <li>
    <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'generateFindingAid')) ?>">
      <i class="fa fa-cogs"></i>
      <?php echo __('Generate') ?>
    </a>
  </li>
<?php endif; ?>

<?php if ($showUpload): ?>
  <li>
    <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'uploadFindingAid')) ?>">
      <i class="fa fa-upload"></i>
      <?php echo __('Upload') ?>
    </a>
  </li>
<?php endif; ?>

<?php if ($showDelete): ?>
  <li>
    <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'deleteFindingAid')) ?>">
      <i class="fa fa-times"></i>
      <?php echo __('Delete') ?>
    </a>
  </li>
<?php endif; ?>

<?php if ($showDownload): ?>
  <li>
    <a class="btn" href="<?php echo public_path($path) ?>" target="_blank">
      <i class="fa fa-file-pdf-o fa-lg"></i>
      <?php echo __('Download'); ?>
    </a>
  </li>
<?php endif; ?>
