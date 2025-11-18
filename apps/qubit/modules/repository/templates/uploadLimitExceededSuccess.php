<h1><?php echo __('Upload limit exceeded'); ?></h1>

<div class="alert alert-danger" role="alert">
  <?php echo __('The upload limit of %1% GB for <a href="%2%">%3%</a> has been exceeded (%4% GB currently used)', [
      '%1%' => $resource->uploadLimit,
      '%2%' => url_for([$resource, 'module' => 'repository']),
      '%3%' => $resource->__toString(),
      '%4%' => $resource->getDiskUsage(['units' => 'G']), ]); ?>
</div>

<div>
  <?php echo __('To upload a new %1%', ['%1%' => strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?>
  <ul>
    <li><?php echo __('Email your <a href="mailto:%1%">system administrator</a> and request a larger upload limit', ['%1%' => QubitUser::getSystemAdmin()->email]); ?></li>
    <li><?php echo __('Delete an existing %1% to reduce disk usage', ['%1%' => strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?></li>
  </ul>
</div>

<section class="actions mb-3">
  <a class="btn atom-btn-outline-light" href="#" onClick="history.back(); return false;"><?php echo __('Back'); ?></a>
</section>
