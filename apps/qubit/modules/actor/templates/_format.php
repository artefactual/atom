<div class="section">

  <h2><?php echo __('Export') ?></h2>

  <div class="content">
    <ul class="clearfix">
      <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfEacPlugin')): ?>
        <li><?php echo link_to(__('EAC'), array($resource, 'module' => 'sfEacPlugin', 'sf_format' => 'xml')) ?></li>
      <?php endif; ?>
    </ul>
  </div>

</div>
