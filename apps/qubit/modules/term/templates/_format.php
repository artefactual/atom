<?php if (in_array('sfSkosPlugin', $sf_data->getRaw('sf_context')->getConfiguration()->getPlugins())): ?>
  <section id="action-icons">
    <ul>

      <?php if (QubitAcl::check($resource, 'create')): ?>
        <li class="separator"><h4><?php echo __('Import') ?></h4></li>
        <li>
          <a href="<?php echo url_for(array($resource, 'module' => 'sfSkosPlugin', 'action' => 'import')) ?>">
            <i class="fa fa-download"></i>
            <?php echo __('SKOS') ?>
          </a>
        </li>
      <?php endif; ?>

      <li class="separator"><h4><?php echo __('Export') ?></h4></li>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfSkosPlugin')) ?>">
          <i class="fa fa-upload"></i>
          <?php echo __('SKOS') ?>
        </a>
      </li>

    </ul>
  </section>
<?php endif; ?>
