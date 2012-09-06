<h1><?php echo __('Upload limit exceeded') ?></h1>

<div class="messages error">
  <?php echo __('The upload limit of %1% GB for <a href="%2%">%3%</a> has been exceeded (%4% GB currently used)', array(
    '%1%' => $resource->uploadLimit,
    '%2%' => url_for(array($resource, 'module' => 'repository')),
    '%3%' => $resource->__toString(),
    '%4%' => $resource->getDiskUsage(array('units' => 'G')))) ?>
</div>

<div>
  <?php echo __('To upload a new %1%', array('%1%' => strtolower(sfConfig::get('app_ui_label_digitalobject')))) ?>
  <ul>
    <li><?php echo __('Email your <a href="mailto:%1%">system administrator</a> and request a larger upload limit', array('%1%' => QubitUser::getSystemAdmin()->email)) ?></li>
    <li><?php echo __('Delete an <a href="%1%">existing %2%</a> to reduce disk usage', array(
      '%1%' => url_for(array(null, 'module' => 'search', 'query' => '+repositorySlug:'.$resource->slug.' +hasDigitalObject:true')),
      '%2%' => strtolower(sfConfig::get('app_ui_label_digitalobject')))) ?></li>
  </ul>
</div>

<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">
      <li><a href="#" onClick="history.back(); return false;"><?php echo __('Back') ?></a></li>
    </ul>
  </div>

</div>
