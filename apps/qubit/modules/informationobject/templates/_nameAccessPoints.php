<div class="field">
  <h3><?php echo __('Name access points') ?></h3>
  <div>
    <ul>

      <?php foreach ($resource->getActorEvents() as $item): ?>
        <?php if (isset($item->actor)): ?>
          <li><?php echo link_to(render_title($item->actor), array($item->actor)) ?> <span class="note2">(<?php echo $item->type->getRole() ?>)</span></li>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php foreach ($resource->relationsRelatedBysubjectId as $item): ?>
        <?php if (isset($item->type) && QubitTerm::NAME_ACCESS_POINT_ID == $item->type->id): ?>
          <li><?php echo link_to(render_title($item->object), array($item->object, 'module' => 'actor')) ?><span class="note2"> (<?php echo __('Subject') ?>)</span></li>
        <?php endif; ?>
      <?php endforeach; ?>

    </ul>
  </div>
</div>
