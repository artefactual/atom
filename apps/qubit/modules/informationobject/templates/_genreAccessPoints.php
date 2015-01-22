<div class="field">

  <?php if (isset($sidebar)): ?>
    <h4><?php echo __('Related genres') ?></h4>
  <?php elseif (isset($mods)): ?>
    <h3><?php echo __('Genres') ?></h3>
  <?php else: ?>
    <h3><?php echo __('Genre access points') ?></h3>
  <?php endif; ?>

  <div>
    <ul>
      <?php foreach ($resource->getTermRelations(QubitTaxonomy::GENRE_ID) as $item): ?>
        <li>
          <?php foreach ($item->term->ancestors->andSelf()->orderBy('lft') as $key => $subject): ?>
            <?php if (QubitTerm::ROOT_ID == $subject->id) continue; ?>
            <?php if (1 < $key): ?>
              &raquo;
            <?php endif; ?>
            <?php echo link_to($subject->__toString(), array($subject, 'module' => 'term')) ?>
          <?php endforeach; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

</div>
