<div id="clipboard-menu"
  data-toggle="tooltip"
  data-title="<?php echo __('Clipboard'); ?>"
  data-load-alert-message="<?php echo __('There was an error loading the clipboard content.'); ?>"
  data-export-alert-message="<?php echo __('The clipboard is empty for this entity type.'); ?>"
  data-export-check-url="<?php echo url_for(['module' => 'clipboard', 'action' => 'exportCheck']); ?>"
  data-delete-alert-message="<?php echo __('Note: clipboard items unclipped in this page will be removed from the clipboard when the page is refreshed. You can re-select them now, or reload the page to remove them completely. Using the sort or print preview buttons will also cause a page reload - so anything currently deselected will be lost!'); ?>">

  <button class="top-item" data-toggle="dropdown" data-target="#" aria-expanded="false">
    <?php echo __('Clipboard'); ?>
  </button>

  <div class="top-dropdown-container">

    <div class="top-dropdown-arrow">
      <div class="arrow"></div>
    </div>

    <div class="top-dropdown-header">
      <h2><?php echo __('Clipboard'); ?></h2>
      <span id="count-block" data-information-object-label="<?php echo sfConfig::get('app_ui_label_informationobject'); ?>" data-actor-object-label="<?php echo sfConfig::get('app_ui_label_actor'); ?>" data-repository-object-label="<?php echo sfConfig::get('app_ui_label_repository'); ?>"></span>
    </div>

    <div class="top-dropdown-body">
      <ul>
        <?php echo QubitMenu::displayHierarchyAsList($menu, 0, ['overrideVisibility' => ['admin' => $sf_user->isAdministrator()]]); ?>
      </ul>
    </div>

    <div class="top-dropdown-bottom"></div>

  </div>

</div>
