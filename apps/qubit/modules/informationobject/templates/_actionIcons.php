<section id="action-icons">
  <ul>

    <li class="separator"><h4><?php echo __('Clipboard'); ?></h4></li>

    <li class="clipboard">
      <?php echo get_component('clipboard', 'button', ['slug' => $resource->slug, 'wide' => true, 'type' => 'informationObject']); ?>
    </li>

    <li class="separator"><h4><?php echo __('Explore'); ?></h4></li>

    <li>
      <a href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'reports']); ?>">
        <i class="fa fa-print"></i>
        <?php echo __('Reports'); ?>
      </a>
    </li>

    <?php if (InformationObjectInventoryAction::showInventory($resource)) { ?>
      <li>
        <a href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'inventory']); ?>">
          <i class="fa fa-list-alt"></i>
          <?php echo __('Inventory'); ?>
        </a>
      </li>
    <?php } ?>

    <li>
      <?php if (isset($resource) && sfConfig::get('app_enable_institutional_scoping') && $sf_user->hasAttribute('search-realm')) { ?>
        <a href="<?php echo url_for([
            'module' => 'informationobject',
            'action' => 'browse',
            'collection' => $resource->getCollectionRoot()->id,
            'repos' => $sf_user->getAttribute('search-realm'),
            'topLod' => false, ]); ?>">
      <?php } else { ?>
        <a href="<?php echo url_for([
            'module' => 'informationobject',
            'action' => 'browse',
            'collection' => $resource->getCollectionRoot()->id,
            'topLod' => false, ]); ?>">
      <?php } ?>

        <i class="fa fa-list"></i>
        <?php echo __('Browse as list'); ?>
      </a>
    </li>

    <?php if (!empty($resource->getDigitalObject())) { ?>
      <li>
        <a href="<?php echo url_for([
            'module' => 'informationobject',
            'action' => 'browse',
            'collection' => $resource->getCollectionRoot()->id,
            'topLod' => false,
            'view' => 'card',
            'onlyMedia' => true, ]); ?>">
          <i class="fa fa-picture-o"></i>
          <?php echo __('Browse digital objects'); ?>
        </a>
      </li>
    <?php } ?>

    <?php if ($sf_user->isAdministrator()) { ?>
      <li class="separator"><h4><?php echo __('Import'); ?></h4></li>
      <li>
        <a href="<?php echo url_for([$resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'xml']); ?>">
          <i class="fa fa-download"></i>
          <?php echo __('XML'); ?>
        </a>
      </li>
      <li>
        <a href="<?php echo url_for([$resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'csv']); ?>">
          <i class="fa fa-download"></i>
          <?php echo __('CSV'); ?>
        </a>
      </li>
    <?php } ?>

    <li class="separator"><h4><?php echo __('Export'); ?></h4></li>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfDcPlugin')) { ?>
      <li>
        <a href="<?php echo $resource->urlForDcExport(); ?>">
          <i class="fa fa-upload"></i>
          <?php echo __('Dublin Core 1.1 XML'); ?>
        </a>
      </li>
    <?php } ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfEadPlugin')) { ?>
      <li>
        <a href="<?php echo $resource->urlForEadExport(); ?>">
          <i class="fa fa-upload"></i>
          <?php echo __('EAD 2002 XML'); ?>
        </a>
      </li>
    <?php } ?>

    <?php if ('sfModsPlugin' == $sf_context->getModuleName() && $sf_context->getConfiguration()->isPluginEnabled('sfModsPlugin')) { ?>
      <li>
        <a href="<?php echo url_for([$resource, 'module' => 'sfModsPlugin', 'sf_format' => 'xml']); ?>">
          <i class="fa fa-upload"></i>
          <?php echo __('MODS 3.5 XML'); ?>
        </a>
      </li>
    <?php } ?>

    <?php echo get_component('informationobject', 'findingAid', ['resource' => $resource]); ?>

    <?php echo get_component('informationobject', 'calculateDatesLink', ['resource' => $resource]); ?>
  </ul>
</section>
