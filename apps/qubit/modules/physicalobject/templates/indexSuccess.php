<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex align-items-center mb-3">
    <a href="<?php echo url_for([$resource, 'module' => 'physicalobject', 'action' => 'boxList']); ?>" class="text-reset">
      <i class="fas fa-3x fa-print me-3" aria-hidden="true"></i>
      <span class="visually-hidden"><?php echo __('Print'); ?></span>
    </a>
    <div class="d-flex flex-column">
      <h1 class="mb-0" aria-describedby="heading-label">
        <?php echo render_title($resource); ?>
      </h1>
      <span class="small" id="heading-label">
        <?php echo __('View %1%', ['%1%' => sfConfig::get('app_ui_label_physicalobject')]); ?>
      </span>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>
<?php end_slot(); ?>

<?php echo render_b5_section_heading(
    sfConfig::get('app_ui_label_physicalobject'),
    true,
    [$resource, 'module' => 'physicalobject', 'action' => 'edit'],
    ['anchor' => 'edit-collapse', 'class' => 'rounded-top']
); ?>

<?php echo render_show(__('Type'), render_value_inline($resource->type)); ?>

<?php echo render_show(__('Location'), render_value_inline($resource->getLocation(['cultureFallback' => true]))); ?>

<?php
    $resources = [];
    $informationObjects = [];
    foreach (QubitRelation::getRelatedObjectsBySubjectId('QubitInformationObject', $resource->id, ['typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID]) as $item) {
      $displayTitle = '';

      if (!empty($item->levelOfDescription)) {
        $displayTitle .= sprintf('%s ', $item->levelOfDescription);
      }

      if (!empty($item->identifier)) {
        $displayTitle .= sprintf('%s ', $item->identifier);
      }

      if (!empty($displayTitle)) {
        $displayTitle .= '- ';
      }

      if (!empty($item->title)) {
        $displayTitle .= $item->title;
      } else {
        $displayTitle .= __('Untitled');
      }

      if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $item->getPublicationStatus()->statusId) {
        $displayTitle .= sprintf(' (%s)', $item->getPublicationStatus());
      }

      $resources[] = link_to(render_title($displayTitle), [$item, 'module' => 'informationobject']);
      $informationObjects[] = $item;
    }
    echo render_show(__('Related resources'), $resources, ['valueClass' => 'field', 'renderAsIs' => true]);
?>

<?php
    $accessions = [];
    $accessionObjects = [];
    foreach (QubitRelation::getRelatedObjectsBySubjectId('QubitAccession', $resource->id, ['typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID]) as $item) {
      $displayTitle = '';

      if (!empty($item->identifier)) {
        $displayTitle .= sprintf('%s - ', $item->identifier);
      }

      if (!empty($item->title)) {
        $displayTitle .= $item->title;
      } else {
        $displayTitle .= __('Untitled');
      }

      $accessions[] = link_to(render_title($displayTitle), [$item, 'module' => 'accession']);
      $accessionObjects[] = $item;
    }
    echo render_show(__('Related accessions'), $accessions, ['valueClass' => 'field', 'renderAsIs' => true]);
?>

<?php slot('after-content'); ?>
  <ul class="actions mb-3 nav gap-2">
    <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'physicalobject', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
    <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'physicalobject', 'action' => 'delete', 'next' => $sf_request->getReferer()], ['class' => 'btn atom-btn-outline-danger']); ?></li>

    <?php if ((count($informationObjects) + count($accessionObjects)) > 0) { ?>
      <li>
        <div class="dropup">
          <button type="button" class="btn atom-btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo __('More'); ?>
          </button>

          <ul class="dropdown-menu mb-2">
            <?php if (count($informationObjects) > 0) { ?>
              <?php $informationObjectSlugs = array_map(fn ($i) => $i->slug, $informationObjects); ?>
              <li>
                <button class="dropdown-item pe-auto"
                        id="add-info-objects-to-clipboard"
                        data-slugs="<?php echo htmlspecialchars(json_encode($informationObjectSlugs), ENT_QUOTES, 'UTF-8'); ?>"
                        data-single-added-message="<?php echo __('Added 1 item to the clipboard'); ?>"
                        data-plural-added-message="<?php echo __('Added %1% items to the clipboard'); ?>"
                        data-already-added-message="<?php echo __('All items are already on the clipboard'); ?>">
                  <?php echo __('Add archival descriptions to clipboard'); ?>
                </button>
              </li>
            <?php } ?>
            <?php if (count($accessionObjects) > 0) { ?>
              <?php $accessionSlugs = array_map(fn ($a) => $a->slug, $accessionObjects); ?>
              <li>
                <button class="dropdown-item pe-auto"
                        id="add-accessions-to-clipboard"
                        data-slugs="<?php echo htmlspecialchars(json_encode($accessionSlugs), ENT_QUOTES, 'UTF-8'); ?>"
                        data-single-added-message="<?php echo __('Added 1 item to the clipboard'); ?>"
                        data-plural-added-message="<?php echo __('Added %1% items to the clipboard'); ?>"
                        data-already-added-message="<?php echo __('All items are already on the clipboard'); ?>">
                  <?php echo __('Add accessions to clipboard'); ?>
                </button>
              </li>
            <?php } ?>
          </ul>
        </div>
      </li>
    <?php } ?>
  </ul>
<?php end_slot(); ?>
