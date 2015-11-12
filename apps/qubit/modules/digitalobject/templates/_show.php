<div class="digital-object-reference">
  <?php if (!empty($accessWarning)): ?>
    <div class="access-warning">
      <?php echo $accessWarning ?>
    </div>
  <?php else: ?>
    <?php echo get_component('digitalobject', $showComponent, array('iconOnly' => $iconOnly, 'link' => $link, 'resource' => $resource, 'usageType' => $usageType)) ?>
  <?php endif; ?>
</div>
