<div class="field<?php echo isset($sidebar) ? '' : ' '.render_b5_show_field_css_classes(); ?>">

  <?php if (isset($sidebar)) { ?>
    <h4 class="h5 mb-2"><?php echo __('Related people and organizations'); ?></h4>
  <?php } elseif (isset($mods)) { ?>
    <?php echo render_b5_show_label(__('Names')); ?>
  <?php } else { ?>
    <?php echo render_b5_show_label(__('Name access points')); ?>
  <?php } ?>

  <div<?php echo isset($sidebar) ? '' : ' class="'.render_b5_show_value_css_classes().'"'; ?>>
    <ul class="<?php echo isset($sidebar) ? 'list-unstyled' : render_b5_show_list_css_classes(); ?>">
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
