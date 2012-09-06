<div class="field">
  <h3><?php echo __('Subject access points') ?></h3>
  <div>
    <ul>
      <?php foreach ($resource->getSubjectAccessPoints() as $item): ?>
        <li><?php echo link_to($item->term, array($item->term, 'module' => 'term', 'action' => 'browseTerm')) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
