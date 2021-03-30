<div class="field">

  <?php if ('rad' == $template) { ?>
    <h3><?php echo __('Related materials'); ?></h3>
  <?php } else { ?>
    <h3><?php echo __('Related descriptions'); ?></h3>
  <?php } ?>

  <div>
    <ul>
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
