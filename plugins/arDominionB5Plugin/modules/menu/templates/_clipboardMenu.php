<li class="nav-item dropdown d-flex flex-column">
  <a
    class="nav-link dropdown-toggle d-flex align-items-center p-0"
    href="#"
    id="<?php echo $menu->getName(); ?>-menu"
    role="button"
    data-bs-toggle="dropdown"
    aria-expanded="false"
    data-total-count-label="<?php echo __('records added'); ?>"
    data-alert-close="<?php echo __('Close'); ?>"
    data-load-alert-message="<?php echo __('There was an error loading the clipboard content.'); ?>"
    data-export-alert-message="<?php echo __('The clipboard is empty for this entity type.'); ?>"
    data-export-check-url="<?php echo url_for(['module' => 'clipboard', 'action' => 'exportCheck']); ?>"
    data-delete-alert-message="<?php echo __(
        'Note: clipboard items unclipped in this page will be removed from the clipboard'
        .' when the page is refreshed. You can re-select them now, or reload the page to'
        .' remove them completely. Using the sort or print preview buttons will also cause'
        .' a page reload - so anything currently deselected will be lost!'
    ); ?>">
    <i
      class="fas fa-2x fa-fw fa-paperclip px-0 px-lg-2 py-2"
      data-bs-toggle="tooltip"
      data-bs-placement="bottom"
      data-bs-custom-class="d-none d-lg-block"
      title="<?php echo $menu->getLabel(['cultureFallback' => true]); ?>"
      aria-hidden="true">
    </i>
    <span class="d-lg-none mx-1" aria-hidden="true">
      <?php echo $menu->getLabel(['cultureFallback' => true]); ?>
    </span>
    <span class="visually-hidden">
      <?php echo $menu->getLabel(['cultureFallback' => true]); ?>
    </span>
  </a>
  <ul class="dropdown-menu dropdown-menu-end mb-2" aria-labelledby="<?php echo $menu->getName(); ?>-menu">
    <li>
      <h6 class="dropdown-header">
        <?php echo $menu->getLabel(['cultureFallback' => true]); ?>
      </h6>
    </li>
    <li class="text-muted text-nowrap px-3 pb-2">
      <span
        id="counts-block"
        data-information-object-label="<?php echo sfConfig::get('app_ui_label_informationobject'); ?>"
        data-actor-object-label="<?php echo sfConfig::get('app_ui_label_actor'); ?>"
        data-repository-object-label="<?php echo sfConfig::get('app_ui_label_repository'); ?>">
      </span>
    </li>
    <?php foreach ($menu->getChildren() as $child) { ?>
      <?php if ($child->checkUserAccess()) { ?>
        <li <?php echo isset($child->name) ? 'id="node_'.$child->name.'"' : ''; ?>>
          <?php echo link_to(
              $child->getLabel(['cultureFallback' => true]),
              $child->getPath(['getUrl' => true, 'resolveAlias' => true]),
              ['class' => 'dropdown-item']
          ); ?>
        </li>
      <?php } ?>
    <?php } ?>
  </ul>
</li>
