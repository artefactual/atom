<div class="field">
  <h3><?php echo __('Creator(s)') ?></h3>
  <div>
    <ul>
      <?php foreach ($ancestor->getCreators() as $item): ?>
        <li>
          <?php if (0 < count($resource->getCreators())): ?>
            <?php echo link_to(render_title($item), array($item)) ?>
          <?php else: ?>
            <?php echo link_to(render_title($item), array($item), array('title' => __('Inherited from %1%', array('%1%' => $ancestor)))) ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
