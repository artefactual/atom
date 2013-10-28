<div class="field">

  <h3><?php echo __('Related descriptions') ?></h3>

  <div>
    <ul>
      <?php foreach ($resource->relationsRelatedBysubjectId as $item): ?>
        <?php if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id): ?>
          <li><?php echo link_to(render_title($item->object), array($item->object, 'module' => 'informationobject')) ?></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </div>

</div>
