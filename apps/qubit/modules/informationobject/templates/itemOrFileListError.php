<div class="content">
    <h1><?php echo __('%1 list', array('%1' => $type)) ?></h1>

    <h1 class="label"><?php echo __('No results') ?></h1>

    <p><?php echo __("Oops, we couldn't find any %1 level descriptions.", array('%1' => strtolower($type))) ?></p>

    <p><?php echo link_to(__('Back'), array($resource, 'module' => 'informationobject', 'action' => 'reports')); ?></p>
  </div>
</div>
