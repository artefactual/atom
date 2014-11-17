<div class="digital-object-reference">
  <?php if ($refMessage): ?>
    <?php echo $refMessage; ?>
  <?php else: ?>
    <?php echo get_component('digitalobject', $showComponent, array('iconOnly' => $iconOnly, 'link' => $link, 'resource' => $resource, 'usageType' => $usageType)) ?>
  <?php endif; ?>
</div>
