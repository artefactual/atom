<?php if (QubitTerm::CHAPTERS_ID == $usageType || QubitTerm::SUBTITLES_ID == $usageType) { ?>
  <?php if (!empty($accessWarning)) { ?>
      <div class="access-warning">
        <?php echo $accessWarning; ?>
      </div>
    <?php } else { ?>
      <?php echo get_component('digitalobject', $showComponent, ['iconOnly' => $iconOnly, 'link' => $link, 'resource' => $resource, 'usageType' => $usageType]); ?>
  <?php } ?>
    
<?php } else { ?>
  <div class="digital-object-reference">
    <?php if (!empty($accessWarning)) { ?>
      <div class="access-warning">
        <?php echo $accessWarning; ?>
      </div>
    <?php } else { ?>
      <?php echo get_component('digitalobject', $showComponent, ['iconOnly' => $iconOnly, 'link' => $link, 'resource' => $resource, 'usageType' => $usageType]); ?>
    <?php } ?>
  </div>
<?php } ?>
