<section id="action-icons">
  <ul>

    <li class="separator"><h4><?php echo __('Clipboard') ?></h4></li>

    <li class="clipboard">
      <?php echo get_component('informationobject', 'clipboardButton', array('slug' => $resource->slug, 'wide' => true)) ?>
    </li>

    <li class="separator"><h4><?php echo __('Explore') ?></h4></li>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'reports')) ?>">
        <i class="icon-print"></i>
        <?php echo __('Reports') ?>
      </a>
    </li>

    <?php if (InformationObjectInventoryAction::showInventory($resource)): ?>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'inventory')) ?>">
          <i class="icon-list-alt"></i>
          <?php echo __('Inventory') ?>
        </a>
      </li>
    <?php endif; ?>

    <li>
      <a href="<?php echo url_for(array(
        'module' => 'informationobject',
        'action' => 'browse',
        'collection' => $resource->getCollectionRoot()->id,
        'topLod' => false)) ?>">
        <i class="icon-list"></i>
        <?php echo __('Browse as list') ?>
      </a>
    </li>

    <li>
      <a href="<?php echo url_for(array(
        $resource->getCollectionRoot(),
        'module' => 'digitalobject',
        'action' => 'browse',
        'collection' => $resource->getCollectionRoot()->id,
        'topLod' => false)) ?>">
        <i class="icon-picture"></i>
        <?php echo __('Browse digital objects') ?>
      </a>
    </li>

    <?php if ($sf_user->isAdministrator()): ?>
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
    <?php endif; ?>

    <li class="separator"><h4><?php echo __('Export') ?></h4></li>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfDcPlugin')): ?>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfDcPlugin', 'sf_format' => 'xml')) ?>">
          <i class="icon-upload-alt"></i>
          <?php echo __('Dublin Core 1.1 XML') ?>
        </a>
      </li>
    <?php endif; ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfEadPlugin')): ?>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfEadPlugin', 'sf_format' => 'xml')) ?>">
          <i class="icon-upload-alt"></i>
          <?php echo __('EAD 2002 XML') ?>
        </a>
      </li>
    <?php endif; ?>

    <?php if ('sfModsPlugin' == $sf_context->getModuleName() && $sf_context->getConfiguration()->isPluginEnabled('sfModsPlugin')): ?>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfModsPlugin', 'sf_format' => 'xml')) ?>">
          <i class="icon-upload-alt"></i>
          <?php echo __('MODS 3.5 XML') ?>
        </a>
      </li>
    <?php endif; ?>

    <?php echo get_component('informationobject', 'findingAid', array('resource' => $resource)) ?>

  </ul>
</section>
