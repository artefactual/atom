<section id="action-icons">
  <ul>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'reports')) ?>">
        <i class="icon-print"></i>
        <?php echo __('Reports') ?>
      </a>
    </li>

    <li class="separator"><h4><?php echo __('Import') ?></h4></li>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'xml')) ?>">
        <i class="icon-download-alt"></i>
        <?php echo __('XML') ?>
      </a>
    </li>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'csv')) ?>">
        <i class="icon-download-alt"></i>
        <?php echo __('CSV') ?>
      </a>
    </li>

    <li class="separator"><h4><?php echo __('Export') ?></h4></li>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'sfDcPlugin', 'sf_format' => 'xml')) ?>">
        <i class="icon-upload-alt"></i>
        <?php echo __('Dublin Core 1.1 XML') ?>
      </a>
    </li>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'sfEadPlugin', 'sf_format' => 'xml')) ?>">
        <i class="icon-upload-alt"></i>
        <?php echo __('EAD 2002 XML') ?>
      </a>
    </li>

    <?php if ('sfModsPlugin' == $sf_context->getModuleName()): ?>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfModsPlugin', 'sf_format' => 'xml')) ?>">
          <i class="icon-upload-alt"></i>
          <?php echo __('MODS 3.3 XML') ?>
        </a>
      </li>
    <?php endif; ?>

  </ul>
</section>
