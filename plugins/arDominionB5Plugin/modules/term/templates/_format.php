<?php if (in_array('sfSkosPlugin', $sf_data->getRaw('sf_context')->getConfiguration()->getPlugins())) { ?>

  <?php if (QubitAcl::check($resource, 'create')) { ?>
    <h4 class="h5 mb-2"><?php echo __('Import'); ?></h4>
    <ul class="list-unstyled">
      <li>
        <a class="atom-icon-link" href="<?php echo url_for([$resource, 'module' => 'sfSkosPlugin', 'action' => 'import']); ?>">
          <i class="fa fa-fw fa-download me-1" aria-hidden="true">
          </i><?php echo __('SKOS'); ?>
        </a>
      </li>
    </ul>
  <?php } ?>

  <h4 class="h5 mb-2"><?php echo __('Export'); ?></h4>
  <ul class="list-unstyled">
    <li>
      <a class="atom-icon-link" href="<?php echo url_for([$resource, 'module' => 'sfSkosPlugin']); ?>">
        <i class="fas fa-fw fa-upload me-1" aria-hidden="true">
        </i><?php echo __('SKOS'); ?>
      </a>
    </li>
  </ul>

<?php } ?>
