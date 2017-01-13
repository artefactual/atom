<h3>
  <?php echo sfConfig::get('app_ui_label_holdings') ?>
  <?php echo image_tag('loading.small.gif', array('class' => 'hidden', 'id' => 'spinner', 'alt' => __('Loading ...'))) ?>
</h3>
<form class="sidebar-search" action="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse')) ?>">
  <input type="hidden" name="repos" value="<?php echo $resource->id ?>">
  <div class="input-prepend input-append">
    <input type="text" name="query" placeholder="<?php echo __('Search holdings') ?>">
    <button class="btn" type="submit">
      <i class="fa fa-search"></i>
    </button>
  </div>
</form>
