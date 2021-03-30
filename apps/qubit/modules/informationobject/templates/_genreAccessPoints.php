<div class="field">

  <?php if (isset($sidebar)) { ?>
    <h4><?php echo __('Related genres'); ?></h4>
  <?php } elseif (isset($mods)) { ?>
    <h3><?php echo __('Genres'); ?></h3>
  <?php } else { ?>
    <h3><?php echo __('Genre access points'); ?></h3>
  <?php } ?>

  <div>
    <ul>
      <?php foreach ($resource->getTermRelations(QubitTaxonomy::GENRE_ID) as $item) { ?>
        <li>
          <?php foreach ($item->term->ancestors->andSelf()->orderBy('lft') as $key => $subject) { ?>
            <?php if (QubitTerm::ROOT_ID == $subject->id) { ?>
              <?php continue; ?>
            <?php } ?>
            <?php if (1 < $key) { ?>
              &raquo;
            <?php } ?>
            <?php echo link_to(render_title($subject), [$subject, 'module' => 'term']); ?>
          <?php } ?>
        </li>
      <?php } ?>
    </ul>
  </div>

</div>
