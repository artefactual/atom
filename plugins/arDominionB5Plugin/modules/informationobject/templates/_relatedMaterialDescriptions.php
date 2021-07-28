<div class="field <?php echo render_b5_show_field_css_classes(); ?>">

  <?php if ('rad' == $template) { ?>
    <?php echo render_b5_show_label(__('Related materials')); ?>
  <?php } else { ?>
    <?php echo render_b5_show_label(__('Related descriptions')); ?>
  <?php } ?>

  <div class="<?php echo render_b5_show_value_css_classes(); ?>">
    <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
      <?php foreach ($resource->relationsRelatedBysubjectId as $item) { ?>
        <?php if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id) { ?>
          <li><?php echo link_to(render_title($item->object), [$item->object, 'module' => 'informationobject']); ?></li>
        <?php } ?>
      <?php } ?>
      <?php foreach ($resource->relationsRelatedByobjectId as $item) { ?>
        <?php if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id) { ?>
          <li><?php echo link_to(render_title($item->subject), [$item->subject, 'module' => 'informationobject']); ?></li>
        <?php } ?>
      <?php } ?>
    </ul>
  </div>

</div>
