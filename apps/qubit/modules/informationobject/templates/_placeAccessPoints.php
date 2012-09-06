<div class="field">
  <h3><?php echo __('Place access points') ?></h3>
  <div>
    <ul>
      <?php foreach ($resource->getPlaceAccessPoints() as $item): ?>
        <li><?php echo link_to($item->term, array($item->term, 'module' => 'term', 'action' => 'browseTerm')) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
