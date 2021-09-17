<section id="action-icons">

  <h4 class="h5 mb-2"><?php echo __('Clipboard'); ?></h4>
  <ul class="list-unstyled">
    <li>
      <?php echo get_component('clipboard', 'button', ['slug' => $resource->slug, 'wide' => true, 'type' => 'informationObject']); ?>
    </li>
  </ul>

  <h4 class="h5 mb-2"><?php echo __('Explore'); ?></h4>
  <ul class="list-unstyled">

    <li>
      <a class="atom-icon-link" href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'reports']); ?>">
        <i class="fas fa-fw fa-print me-1" aria-hidden="true">
        </i><?php echo __('Reports'); ?>
      </a>
    </li>

    <?php if (InformationObjectInventoryAction::showInventory($resource)) { ?>
      <li>
        <a class="atom-icon-link" href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'inventory']); ?>">
          <i class="fas fa-fw fa-list-alt me-1" aria-hidden="true">
          </i><?php echo __('Inventory'); ?>
        </a>
      </li>
    <?php } ?>

    <li>
      <?php if (isset($resource) && sfConfig::get('app_enable_institutional_scoping') && $sf_user->hasAttribute('search-realm')) { ?>
        <a class="atom-icon-link" href="<?php echo url_for([
            'module' => 'informationobject',
            'action' => 'browse',
            'collection' => $resource->getCollectionRoot()->id,
            'repos' => $sf_user->getAttribute('search-realm'),
            'topLod' => false, ]); ?>">
      <?php } else { ?>
        <a class="atom-icon-link" href="<?php echo url_for([
            'module' => 'informationobject',
            'action' => 'browse',
            'collection' => $resource->getCollectionRoot()->id,
            'topLod' => false, ]); ?>">
      <?php } ?>
        <i class="fas fa-fw fa-list me-1" aria-hidden="true">
        </i><?php echo __('Browse as list'); ?>
      </a>
    </li>

    <?php if (!empty($resource->getDigitalObject())) { ?>
      <li>
        <a class="atom-icon-link" href="<?php echo url_for([
            'module' => 'informationobject',
            'action' => 'browse',
            'collection' => $resource->getCollectionRoot()->id,
            'topLod' => false,
            'view' => 'card',
            'onlyMedia' => true, ]); ?>">
          <i class="fas fa-fw fa-image me-1" aria-hidden="true">
          </i><?php echo __('Browse digital objects'); ?>
        </a>
      </li>
    <?php } ?>
  </ul>

  <?php if ($sf_user->isAdministrator()) { ?>
    <h4 class="h5 mb-2"><?php echo __('Import'); ?></h4>
    <ul class="list-unstyled">
      <li>
        <a class="atom-icon-link" href="<?php echo url_for([$resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'xml']); ?>">
          <i class="fas fa-fw fa-download me-1" aria-hidden="true">
          </i><?php echo __('XML'); ?>
        </a>
      </li>

      <li>
        <a class="atom-icon-link" href="<?php echo url_for([$resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'csv']); ?>">
          <i class="fas fa-fw fa-download me-1" aria-hidden="true">
          </i><?php echo __('CSV'); ?>
        </a>
      </li>
    </ul>
  <?php } ?>

  <h4 class="h5 mb-2"><?php echo __('Export'); ?></h4>
  <ul class="list-unstyled">
    <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfDcPlugin')) { ?>
      <li>
        <a class="atom-icon-link" href="<?php echo $resource->urlForDcExport(); ?>">
          <i class="fas fa-fw fa-upload me-1" aria-hidden="true">
          </i><?php echo __('Dublin Core 1.1 XML'); ?>
        </a>
      </li>
    <?php } ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfEadPlugin')) { ?>
      <li>
        <a class="atom-icon-link" href="<?php echo $resource->urlForEadExport(); ?>">
          <i class="fas fa-fw fa-upload me-1" aria-hidden="true">
          </i><?php echo __('EAD 2002 XML'); ?>
        </a>
      </li>
    <?php } ?>

    <?php if ('sfModsPlugin' == $sf_context->getModuleName() && $sf_context->getConfiguration()->isPluginEnabled('sfModsPlugin')) { ?>
      <li>
        <a class="atom-icon-link" href="<?php echo url_for([$resource, 'module' => 'sfModsPlugin', 'sf_format' => 'xml']); ?>">
          <i class="fas fa-fw fa-upload me-1" aria-hidden="true">
          </i><?php echo __('MODS 3.5 XML'); ?>
        </a>
      </li>
    <?php } ?>
  </ul>

  <?php echo get_component('informationobject', 'findingAid', ['resource' => $resource, 'contextMenu' => true]); ?>

  <?php echo get_component('informationobject', 'calculateDatesLink', ['resource' => $resource, 'contextMenu' => true]); ?>

</section>
