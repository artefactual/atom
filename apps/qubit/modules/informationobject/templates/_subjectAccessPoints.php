<div class="field">

  <?php if (isset($sidebar)): ?>
    <h4><?php echo __('Related subjects') ?></h4>
  <?php else: ?>
    <h3><?php echo __('Subject access points') ?></h3>
  <?php endif; ?>

  <div>
    <ul>
      <?php foreach ($resource->getSubjectAccessPoints() as $item): ?>
        <li><?php echo link_to($item->term, array($item->term, 'module' => 'term', 'action' => 'browseTerm')) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

</div>
