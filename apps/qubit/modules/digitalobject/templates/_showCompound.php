<table class="compound_digiobj">
  <tbody>

    <tr>
      <td>
        <?php if (null !== $representation = $leftObject->getCompoundRepresentation()): ?>
          <?php if ($resource->object instanceOf QubitInformationObject): ?>
            <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject') || QubitTerm::TEXT_ID == $resource->mediaType->id, image_tag($representation->getFullPath(), array('alt' => '')), public_path($leftObject->getFullPath(), array('title' => __('View full size')))) ?>
          <?php elseif ($resource->object instanceOf QubitActor): ?>
            <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'actor') || QubitTerm::TEXT_ID == $resource->mediaType->id, image_tag($representation->getFullPath(), array('alt' => '')), public_path($leftObject->getFullPath(), array('title' => __('View full size')))) ?>
          <?php endif; ?>
        <?php endif; ?>
      </td><td>
        <?php if (null !== $rightObject && null !== $representation = $rightObject->getCompoundRepresentation()): ?>
          <?php if ($resource->object instanceOf QubitInformationObject): ?>
            <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject') || QubitTerm::TEXT_ID == $resource->mediaType->id, image_tag($representation->getFullPath(), array('alt' => '')), public_path($rightObject->getFullPath(), array('title' => __('View full size')))) ?>
          <?php elseif ($resource->object instanceOf QubitActor): ?>
            <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'actor') || QubitTerm::TEXT_ID == $resource->mediaType->id, image_tag($representation->getFullPath(), array('alt' => '')), public_path($rightObject->getFullPath(), array('title' => __('View full size')))) ?>
          <?php endif; ?>
        <?php endif; ?>
      </td>
    </tr>

    <?php if (($resource->object instanceOf QubitInformationObject && SecurityPrivileges::editCredentials($sf_user, 'informationObject')) || ($resource->object instanceOf QubitActor && SecurityPrivileges::editCredentials($sf_user, 'actor'))): ?>
      <tr>
        <td colspan="2" class="download_link">
          <?php echo link_to(__('Download %1%', array('%1%' => $resource)), public_path($resource->getFullPath()), array('class' => 'download')) ?>
        </td>
      </tr>
    <?php endif; ?>

  </tbody>
</table>

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>
