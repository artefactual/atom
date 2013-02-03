<section id="action-icons">
  <ul>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'reports')) ?>">
        <?php echo image_tag('report.png') ?>
        <?php echo __('Reports') ?>
      </a>
    </li>

    <li class="separator"><?php echo __('Import') ?></li>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'xml')) ?>">
        <?php echo image_tag('report.png') ?>
        <?php echo __('XML') ?>
      </a>
    </li>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'csv')) ?>">
        <?php echo image_tag('report.png') ?>
        <?php echo __('CSV') ?>
      </a>
    </li>

    <li class="separator"><?php echo __('Export') ?></li>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'sfDcPlugin', 'sf_format' => 'xml')) ?>">
        <?php echo image_tag('report.png') ?>
        <?php echo __('Dublin Core 1.1 XML') ?>
      </a>
    </li>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'sfEadPlugin', 'sf_format' => 'xml')) ?>">
        <?php echo image_tag('report.png') ?>
        <?php echo __('EAD 2002 XML') ?>
      </a>
    </li>

    <?php if ('sfModsPlugin' == $sf_context->getModuleName()): ?>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfModsPlugin', 'sf_format' => 'xml')) ?>">
          <?php echo image_tag('report.png') ?>
          <?php echo __('MODS 3.3 XML') ?>
        </a>
      </li>
    <?php endif; ?>

  </ul>
</section>
