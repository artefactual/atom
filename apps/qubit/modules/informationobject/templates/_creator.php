<div class="field">
  <h3><?php echo __('Creator(s)'); ?></h3>
  <div>
    <ul>
      <?php foreach ($ancestor->getCreators() as $item) { ?>
        <li>
          <?php if (0 < count($resource->getCreators())) { ?>
            <?php echo link_to(render_title($item), [$item]); ?>
          <?php } else { ?>
            <?php echo link_to(render_title($item), [$item], ['title' => __('Inherited from %1%', ['%1%' => $ancestor])]); ?>
          <?php } ?>
        </li>
      <?php } ?>
    </ul>
  </div>
</div>
