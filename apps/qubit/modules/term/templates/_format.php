<?php if (in_array('sfSkosPlugin', $sf_context->getConfiguration()->getPlugins())): ?>

  <?php if (QubitAcl::check($resource, 'create')): ?>
    <div class="list-menu">

      <h4><?php echo __('Import') ?></h4>

      <div class="content">
        <ul class="clearfix">
          <li><?php echo link_to(__('SKOS'), array($resource, 'module' => 'sfSkosPlugin', 'action' => 'import')) ?></li>
        </ul>
      </div>

    </div>
  <?php endif; ?>

  <div class="list-menu">

    <h4><?php echo __('Export') ?></h4>

    <div class="content">
      <ul class="clearfix">
        <li><?php echo link_to(__('SKOS'), array($resource, 'module' => 'sfSkosPlugin')) ?></li>
      </ul>
    </div>

  </div>
<?php endif; ?>
