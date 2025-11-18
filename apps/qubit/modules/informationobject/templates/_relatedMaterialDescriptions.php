<div class="field <?php echo render_b5_show_field_css_classes(); ?>">

  <?php if ('rad' == $template) { ?>
    <?php echo render_b5_show_label(__('Related materials')); ?>
  <?php } else { ?>
    <?php echo render_b5_show_label(__('Related descriptions')); ?>
  <?php } ?>

  <div class="<?php echo render_b5_show_value_css_classes(); ?>">
    <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
      <?php foreach ($resource->relationsRelatedBysubjectId as $item) { ?>
          <?php if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->getTypeId()) { ?>
            <?php if ($sf_user->isAuthenticated() || QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID == $item->object->getPublicationStatus()->statusId) { ?>
              <?php $itemTitle = $item->object->__toString(); ?>

              <?php if (isset($item->object->levelOfDescription) || isset($item->object->identifier)) { ?>
                <?php $itemTitle = "- {$itemTitle}"; ?>
                  <?php if (isset($item->object->identifier)) { ?>
                    <?php $itemTitle = "{$item->object->referenceCode} {$itemTitle}"; ?>
                  <?php } ?>
                  <?php if (isset($item->object->levelOfDescription)) { ?>
                    <?php $itemTitle = "{$item->object->levelOfDescription} {$itemTitle}"; ?>
                  <?php } ?>
              <?php } ?>

              <?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $item->object->getPublicationStatus()->statusId) { ?>
                <?php $itemTitle .= " ({$item->object->getPublicationStatus()})"; ?>
              <?php } ?>

              <li><?php echo link_to($itemTitle, [$item->object, 'module' => 'informationobject']); ?></li>

            <?php } ?>
          <?php } ?>
        <?php } ?>

        <?php foreach ($resource->relationsRelatedByobjectId as $item) { ?>
          <?php if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->getTypeId()) { ?>
            <?php if ($sf_user->isAuthenticated() || QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID == $item->subject->getPublicationStatus()->statusId) { ?>
              <?php $itemTitle = $item->subject->__toString(); ?>

              <?php if (isset($item->subject->levelOfDescription) || isset($item->subject->identifier)) { ?>
                <?php $itemTitle = "- {$itemTitle}"; ?>
                  <?php if (isset($item->subject->identifier)) { ?>
                    <?php $itemTitle = "{$item->subject->referenceCode} {$itemTitle}"; ?>
                  <?php } ?>
                  <?php if (isset($item->subject->levelOfDescription)) { ?>
                    <?php $itemTitle = "{$item->subject->levelOfDescription} {$itemTitle}"; ?>
                  <?php } ?>
              <?php } ?>

              <?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $item->subject->getPublicationStatus()->statusId) { ?>
                <?php $itemTitle .= " ({$item->subject->getPublicationStatus()})"; ?>
              <?php } ?>

              <li><?php echo link_to($itemTitle, [$item->subject, 'module' => 'informationobject']); ?></li>

            <?php } ?>
          <?php } ?>
        <?php } ?>
    </ul>
  </div>

</div>
