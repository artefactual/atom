<?php if (in_array('sfSkosPlugin', $sf_context->getConfiguration()->getPlugins())): ?>

  <?php if (QubitAcl::check($resource, 'create')): ?>
    <div class="section">

      <h2><?php echo __('Import') ?></h2>

      <div class="content">
        <ul class="clearfix">
          <li><?php echo link_to(__('SKOS'), array($resource, 'module' => 'sfSkosPlugin', 'action' => 'import')) ?></li>
        </ul>
      </div>

    </div>
  <?php endif; ?>

  <div class="section">

    <h2><?php echo __('Export') ?></h2>

    <div class="content">
      <ul class="clearfix">
        <li><?php echo link_to(__('SKOS'), array($resource, 'module' => 'sfSkosPlugin', 'sf_format' => 'xml')) ?></li>
      </ul>
    </div>

  </div>
<?php endif; ?>
