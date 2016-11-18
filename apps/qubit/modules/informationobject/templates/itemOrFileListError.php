<div class="content">
    <h1><?php echo __($type.' list') ?></h1>

    <h1 class="label"><?php echo __('No results') ?></h1>

    <p><?php echo __("Oops, we couldn't find any ".strtolower($type).' level descriptions.') ?></p>

    <p><?php echo link_to(__('Back'), array($resource, 'module' => 'informationobject', 'action' => 'reports')); ?></p>
  </div>
</div>
