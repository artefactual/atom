<div class="field<?php echo isset($sidebar) ? '' : ' '.render_b5_show_field_css_classes(); ?>">

  <?php if (isset($sidebar)) { ?>
    <h4 class="h5 mb-2"><?php echo __('Related places'); ?></h4>
  <?php } elseif (isset($mods)) { ?>
    <?php echo render_b5_show_label(__('Places')); ?>
  <?php } else { ?>
    <?php echo render_b5_show_label(__('Place access points')); ?>
  <?php } ?>

  <div<?php echo isset($sidebar) ? '' : ' class="'.render_b5_show_value_css_classes().'"'; ?>>
    <ul class="<?php echo isset($sidebar) ? 'list-unstyled' : render_b5_show_list_css_classes(); ?>">
      <?php foreach ($resource->getPlaceAccessPoints() as $item) { ?>
        <li>
          <?php foreach ($item->term->ancestors->andSelf()->orderBy('lft') as $key => $place) { ?>
            <?php if (QubitTerm::ROOT_ID == $place->id) { ?>
              <?php continue; ?>
            <?php } ?>
            <?php if (1 < $key) { ?>
              &raquo;
            <?php } ?>
            <?php if ('QubitActor' == $resource->getClass()) { ?>
              <?php echo link_to(render_title($place), [$place, 'module' => 'term', 'action' => 'relatedAuthorities']); ?>
            <?php } else { ?>
              <?php echo link_to(render_title($place), [$place, 'module' => 'term']); ?>
            <?php } ?>
          <?php } ?>
        </li>
      <?php } ?>
    </ul>
  </div>

</div>
