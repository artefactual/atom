<div class="field">

  <?php if (isset($sidebar)) { ?>
    <h4><?php echo __('Related subjects'); ?></h4>
  <?php } elseif (isset($mods)) { ?>
    <h3><?php echo __('Subjects'); ?></h3>
  <?php } else { ?>
    <h3><?php echo __('Subject access points'); ?></h3>
  <?php } ?>

  <div>
    <ul>
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
