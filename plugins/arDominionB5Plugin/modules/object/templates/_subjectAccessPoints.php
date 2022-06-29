<div class="field<?php echo isset($sidebar) ? '' : ' '.render_b5_show_field_css_classes(); ?>">

  <?php if (isset($sidebar)) { ?>
    <h4 class="h5 mb-2"><?php echo __('Related subjects'); ?></h4>
  <?php } elseif (isset($mods)) { ?>
    <?php echo render_b5_show_label(__('Subjects')); ?>
  <?php } else { ?>
    <?php echo render_b5_show_label(__('Subject access points')); ?>
  <?php } ?>

  <div<?php echo isset($sidebar) ? '' : ' class="'.render_b5_show_value_css_classes().'"'; ?>>
    <ul class="<?php echo isset($sidebar) ? 'list-unstyled' : render_b5_show_list_css_classes(); ?>">
      <?php foreach ($resource->getSubjectAccessPoints() as $item) { ?>
        <li>
          <?php foreach ($item->term->ancestors->andSelf()->orderBy('lft') as $key => $subject) { ?>
            <?php if (QubitTerm::ROOT_ID == $subject->id) { ?>
              <?php continue; ?>
            <?php } ?>
            <?php if (1 < $key) { ?>
              &raquo;
            <?php } ?>
            <?php if ('QubitActor' == $resource->getClass()) { ?>
              <?php echo link_to(render_title($subject), [$subject, 'module' => 'term', 'action' => 'relatedAuthorities']); ?>
            <?php } else { ?>
              <?php echo link_to(render_title($subject), [$subject, 'module' => 'term']); ?>
            <?php } ?>
          <?php } ?>
        </li>
      <?php } ?>
    </ul>
  </div>

</div>
