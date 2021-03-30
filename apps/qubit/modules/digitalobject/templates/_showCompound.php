<table class="compound_digiobj">
  <tbody>

    <tr>
      <td>
        <?php if (null !== $representation = $leftObject->getCompoundRepresentation()) { ?>
          <?php if ($resource->object instanceof QubitInformationObject) { ?>
            <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject') || QubitTerm::TEXT_ID == $resource->mediaType->id, image_tag($representation->getFullPath(), ['alt' => '']), public_path($leftObject->getFullPath(), ['title' => __('View full size')])); ?>
          <?php } elseif ($resource->object instanceof QubitActor) { ?>
            <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'actor') || QubitTerm::TEXT_ID == $resource->mediaType->id, image_tag($representation->getFullPath(), ['alt' => '']), public_path($leftObject->getFullPath(), ['title' => __('View full size')])); ?>
          <?php } ?>
        <?php } ?>
      </td><td>
        <?php if (null !== $rightObject && null !== $representation = $rightObject->getCompoundRepresentation()) { ?>
          <?php if ($resource->object instanceof QubitInformationObject) { ?>
            <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject') || QubitTerm::TEXT_ID == $resource->mediaType->id, image_tag($representation->getFullPath(), ['alt' => '']), public_path($rightObject->getFullPath(), ['title' => __('View full size')])); ?>
          <?php } elseif ($resource->object instanceof QubitActor) { ?>
            <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'actor') || QubitTerm::TEXT_ID == $resource->mediaType->id, image_tag($representation->getFullPath(), ['alt' => '']), public_path($rightObject->getFullPath(), ['title' => __('View full size')])); ?>
          <?php } ?>
        <?php } ?>
      </td>
    </tr>

    <?php if (($resource->object instanceof QubitInformationObject && SecurityPrivileges::editCredentials($sf_user, 'informationObject')) || ($resource->object instanceof QubitActor && SecurityPrivileges::editCredentials($sf_user, 'actor'))) { ?>
      <tr>
        <td colspan="2" class="download_link">
          <?php echo link_to(__('Download %1%', ['%1%' => $resource]), public_path($resource->getFullPath()), ['class' => 'download']); ?>
        </td>
      </tr>
    <?php } ?>

  </tbody>
</table>

<?php echo get_partial('default/pager', ['pager' => $pager]); ?>
