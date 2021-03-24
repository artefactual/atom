<?php if ($usageType == QubitTerm::CHAPTERS_ID || $usageType == QubitTerm::SUBTITLES_ID): ?>
  <?php if (!empty($accessWarning)): ?>
      <div class="access-warning">
        <?php echo $accessWarning ?>
      </div>
    <?php else: ?>
      <?php echo get_component('digitalobject', $showComponent, array('iconOnly' => $iconOnly, 'link' => $link, 'resource' => $resource, 'usageType' => $usageType)) ?>
  <?php endif; ?>
    
<?php else: ?>
  <div class="digital-object-reference">
    <?php if (!empty($accessWarning)): ?>
      <div class="access-warning">
        <?php echo $accessWarning ?>
      </div>
    <?php else: ?>
      <?php echo get_component('digitalobject', $showComponent, array('iconOnly' => $iconOnly, 'link' => $link, 'resource' => $resource, 'usageType' => $usageType)) ?>
    <?php endif; ?>
  </div>
<?php endif; ?>
