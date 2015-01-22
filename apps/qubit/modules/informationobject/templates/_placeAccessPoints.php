<div class="field">

  <?php if (isset($sidebar)): ?>
    <h4><?php echo __('Related places') ?></h4>
  <?php elseif (isset($mods)): ?>
    <h3><?php echo __('Places') ?></h3>
  <?php else: ?>
    <h3><?php echo __('Place access points') ?></h3>
  <?php endif; ?>

  <div>
    <ul>
      <?php foreach ($resource->getPlaceAccessPoints() as $item): ?>
        <li>
          <?php foreach ($item->term->ancestors->andSelf()->orderBy('lft') as $key => $place): ?>
            <?php if (QubitTerm::ROOT_ID == $place->id) continue; ?>
            <?php if (1 < $key): ?>
              &raquo;
            <?php endif; ?>
            <?php echo link_to($place->__toString(), array($place, 'module' => 'term')) ?>
          <?php endforeach; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

</div>
