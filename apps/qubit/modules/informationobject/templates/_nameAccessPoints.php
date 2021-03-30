<div class="field">

  <?php if (isset($sidebar)) { ?>
    <h4><?php echo __('Related people and organizations'); ?></h4>
  <?php } elseif (isset($mods)) { ?>
    <h3><?php echo __('Names'); ?></h3>
  <?php } else { ?>
    <h3><?php echo __('Name access points'); ?></h3>
  <?php } ?>

  <div>
    <ul>
      <?php if (isset($showActorEvents) || isset($sidebar)) { ?>
        <?php $actorsShown = []; ?>
        <?php foreach ($resource->getActorEvents() as $item) { ?>
          <?php if (isset($sidebar) || QubitTerm::CREATION_ID != $item->type->id) { ?>
            <?php if (isset($item->actor) && !isset($actorsShown[$item->actor->id])) { ?>
              <li><?php echo link_to(render_title($item->actor), [$item->actor]); ?> <span class="note2">(<?php echo render_value_inline($item->type->getRole()); ?>)</span></li>
              <?php $actorsShown[$item->actor->id] = true; ?>
            <?php } ?>
          <?php } ?>
        <?php } ?>
      <?php } ?>

      <?php foreach ($resource->relationsRelatedBysubjectId as $item) { ?>
        <?php if (isset($item->type) && QubitTerm::NAME_ACCESS_POINT_ID == $item->type->id) { ?>
          <li><?php echo link_to(render_title($item->object), [$item->object, 'module' => 'actor']); ?><span class="note2"> <?php if (!isset($mods)) { ?>(<?php echo __('Subject'); ?>)<?php } ?></span></li>
        <?php } ?>
      <?php } ?>
    </ul>
  </div>

</div>
