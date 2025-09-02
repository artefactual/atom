<form class="mb-3" role="search" aria-label="<?php echo sfConfig::get('app_ui_label_holdings'); ?>" action="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse']); ?>">
  <input type="hidden" name="repos" value="<?php echo $resource->id; ?>">
  <div class="input-group">
    <input type="text" class="form-control" name="query" aria-label="<?php echo __('Search'); ?>" placeholder="<?php echo __('Search'); ?>">
    <button class="btn atom-btn-white" type="submit" aria-label="<?php echo __('Search'); ?>">
      <i aria-hidden="true" class="fas fa-search"></i>
    </button>
  </div>
</form>
