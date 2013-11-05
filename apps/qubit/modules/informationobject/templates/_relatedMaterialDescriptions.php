<div class="field">

  <?php if ($template == 'rad'): ?>
    <h3><?php echo __('Related materials') ?></h3>
  <?php else: ?>
    <h3><?php echo __('Related descriptions') ?></h3>
  <?php endif; ?>

  <div>
    <ul>
      <?php foreach ($resource->relationsRelatedBysubjectId as $item): ?>
        <?php if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id): ?>
          <li><?php echo link_to(render_title($item->object), array($item->object, 'module' => 'informationobject')) ?></li>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php foreach ($resource->relationsRelatedByobjectId as $item): ?>
        <?php if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id): ?>
          <li><?php echo link_to(render_title($item->subject), array($item->subject, 'module' => 'informationobject')) ?></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </div>

</div>
