<div class="field">

  <?php if (isset($sidebar)) { ?>
    <h4><?php echo __('Related places'); ?></h4>
  <?php } elseif (isset($mods)) { ?>
    <h3><?php echo __('Places'); ?></h3>
  <?php } else { ?>
    <h3><?php echo __('Place access points'); ?></h3>
  <?php } ?>

  <div>
    <ul>
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
