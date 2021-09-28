<?php if (QubitTerm::CHAPTERS_ID == $usageType || QubitTerm::SUBTITLES_ID == $usageType) { ?>
  <?php if (!empty($accessWarning)) { ?>
      <div class="access-warning">
        <?php echo $accessWarning; ?>
      </div>
    <?php } else { ?>
      <?php echo get_component('digitalobject', $showComponent, ['iconOnly' => $iconOnly, 'link' => $link, 'resource' => $resource, 'usageType' => $usageType]); ?>
  <?php } ?>
    
<?php } else { ?>
  <div class="digital-object-reference p-3 border-bottom text-center">
    <?php if (!empty($accessWarning)) { ?>
      <div class="access-warning">
        <?php echo $accessWarning; ?>
      </div>
    <?php } else { ?>
      <?php $preservicaUUID = $resource->getPropertyByName(arUnogPreservicaPluginConfiguration::PRESERVICA_UUID_PROPERTY_NAME)->__toString(); ?>
      <?php if (!empty($preservicaUUID)) { ?>
        <?php $link = url_for([$resource->object, 'sf_route' => 'preservica_download_master']); ?>
      <?php } ?>
      <?php echo get_component('digitalobject', $showComponent, ['iconOnly' => $iconOnly, 'link' => $link, 'resource' => $resource, 'usageType' => $usageType]); ?>
    <?php } ?>
  </div>
<?php } ?>
