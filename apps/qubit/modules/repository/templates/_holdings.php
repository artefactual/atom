<h3>
  <?php echo sfConfig::get('app_ui_label_holdings'); ?>
  <?php echo image_tag('loading.small.gif', ['class' => 'hidden', 'id' => 'spinner', 'alt' => __('Loading ...')]); ?>
</h3>
<form class="sidebar-search" role="search" aria-label="<?php echo sfConfig::get('app_ui_label_holdings'); ?>" action="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse']); ?>">
  <input type="hidden" name="repos" value="<?php echo $resource->id; ?>">
  <div class="input-prepend input-append">
    <input type="text" name="query" aria-label="<?php echo __('Search %1%', ['%1%' => sfConfig::get('app_ui_label_holdings')]); ?>" placeholder="<?php echo __('Search %1%', ['%1%' => strtolower(sfConfig::get('app_ui_label_holdings'))]); ?>">
    <button class="btn" type="submit" aria-label=<?php echo __('Search'); ?>>
      <i aria-hidden="true" class="fa fa-search"></i>
    </button>
  </div>
</form>
