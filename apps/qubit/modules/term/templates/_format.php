<?php if (in_array('sfSkosPlugin', $sf_context->getConfiguration()->getPlugins())): ?>
  <section id="action-icons">
    <ul>

      <?php if (QubitAcl::check($resource, 'create')): ?>
        <li class="separator"><h4><?php echo __('Import') ?></h4></li>
        <li>
          <a href="<?php echo url_for(array($resource, 'module' => 'sfSkosPlugin', 'action' => 'import')) ?>">
            <i class="icon-download-alt"></i>
            <?php echo __('SKOS') ?>
          </a>
        </li>
      <?php endif; ?>

      <li class="separator"><h4><?php echo __('Export') ?></h4></li>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfSkosPlugin')) ?>">
          <i class="icon-upload-alt"></i>
          <?php echo __('SKOS') ?>
        </a>
      </li>

    </ul>
  </section>
<?php endif; ?>
