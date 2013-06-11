<div class="section">

  <h2><?php echo __('Export') ?></h2>

  <div class="content">
    <ul class="clearfix">

      <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfDcPlugin')): ?>
        <li><?php echo link_to(__('Dublin Core 1.1 XML'), array($resource, 'module' => 'sfDcPlugin', 'sf_format' => 'xml')) ?></li>
      <?php endif; ?>

      <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfEadPlugin')): ?>
        <li><?php echo link_to(__('EAD 2002 XML'), array($resource, 'module' => 'sfEadPlugin', 'sf_format' => 'xml')) ?></li>
      <?php endif; ?>

      <?php if ('sfModsPlugin' == $sf_context->getModuleName() && $sf_context->getConfiguration()->isPluginEnabled('sfModsPlugin')): ?>
        <li><?php echo link_to(__('MODS 3.3 XML'), array($resource, 'module' => 'sfModsPlugin', 'sf_format' => 'xml')) ?></li>
      <?php endif; ?>

    </ul>
  </div>

</div>
