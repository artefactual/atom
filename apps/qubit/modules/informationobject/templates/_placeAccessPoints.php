<div class="field">

  <?php if (isset($sidebar)): ?>
    <h4><?php echo __('Related people and organizations') ?></h4>
  <?php else: ?>
    <h3><?php echo __('Place access points') ?></h3>
  <?php endif; ?>

  <div>
    <ul>
      <?php foreach ($resource->getPlaceAccessPoints() as $item): ?>
        <li><?php echo link_to($item->term, array($item->term, 'module' => 'term', 'action' => 'browseTerm')) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

</div>
