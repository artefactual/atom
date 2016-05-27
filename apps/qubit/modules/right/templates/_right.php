<div class="field">
  <h3><?php echo __('Related right') ?></h3>
  <?php if (QubitAcl::check($relatedObject, 'update')): ?>
    <a href="<?php echo url_for(array('module' => 'right', 'action' => 'edit', 'slug' => $resource->slug)) ?>">&nbsp;Edit</a> |
    <a href="<?php echo url_for(array('module' => 'right', 'action' => 'delete', 'slug' => $resource->slug)) ?>"  class="deleteRightBasis">Delete</a>
  <?php endif; ?>

  <div>
    <?php if (isset($inherit)): ?>
      <?php echo link_to(render_title($inherit), array($inherit, 'module' => 'informationobject'), array('title' => __('Inherited from %1%', array('%1%' => $inherit)))) ?>
    <?php endif; ?>

    <?php echo render_show(__('Basis'), render_value($resource->basis)) ?>

    <?php echo render_show(__('Start date'), render_value(Qubit::renderDate($resource->startDate))) ?>

    <?php echo render_show(__('End date'), render_value(Qubit::renderDate($resource->endDate))) ?>

    <?php echo render_show(__('Documentation Identifier Type'), render_value($resource->identifierType)) ?>

    <?php echo render_show(__('Documentation Identifier Value'), render_value($resource->identifierValue)) ?>

    <?php echo render_show(__('Documentation Identifier Role'), render_value($resource->identifierRole)) ?>

    <?php if (isset($resource->rightsHolder)): ?>
      <?php echo render_show(__('Rights holder'), link_to(render_value($resource->rightsHolder), array($resource->rightsHolder, 'module' => 'rightsholder'))) ?>
    <?php endif; ?>

    <?php echo render_show(__('Rights note(s)'), render_value($resource->getRightsNote(array('cultureFallback' => true)))) ?>

    <?php if (QubitTerm::RIGHT_BASIS_COPYRIGHT_ID == $resource->basisId): ?>

      <?php echo render_show(__('Copyright status'), render_value($resource->copyrightStatus)) ?>

      <?php echo render_show(__('Copyright status determination date'), render_value($resource->copyrightStatusDate)) ?>

      <?php echo render_show(__('Copyright jurisdiction'), render_value(format_country($resource->copyrightJurisdiction))) ?>

      <?php echo render_show(__('Copyright note'), render_value($resource->getCopyrightNote(array('cultureFallback' => true)))) ?>

    <?php elseif (QubitTerm::RIGHT_BASIS_LICENSE_ID == $resource->basisId): ?>

      <?php echo render_show(__('License identifier'), render_value($resource->getIdentifierValue(array('cultureFallback' => true)))) ?>

      <?php echo render_show(__('License terms'), render_value($resource->getLicenseTerms(array('cultureFallback' => true)))) ?>

      <?php echo render_show(__('License note'), render_value($resource->getLicenseNote(array('cultureFallback' => true)))) ?>

    <?php elseif (QubitTerm::RIGHT_BASIS_STATUTE_ID == $resource->basisId): ?>

      <?php echo render_show(__('Statute jurisdiction'), render_value($resource->getStatuteJurisdiction(array('cultureFallback' => true)))) ?>

      <?php if (null !== $statuteCitation = $resource->statuteCitation): ?>
        <?php echo render_show(__('Statute citation'), render_value($statuteCitation->getName(array('cultureFallback' => true)))) ?>
      <?php endif; ?>

      <?php echo render_show(__('Statute determination date'), render_value($resource->statuteDeterminationDate)) ?>

      <?php echo render_show(__('Statute note'), render_value($resource->getStatuteNote(array('cultureFallback' => true)))) ?>

    <?php endif; ?>

    <blockquote>
      <?php foreach ($resource->grantedRights as $grantedRight): ?>
        <hr />
        <?php echo render_show(__('Act'), render_value($grantedRight->act)) ?>
        <?php echo render_show(__('Restriction'), render_value(QubitGrantedRight::getRestrictionString($grantedRight->restriction))) ?>
        <?php echo render_show(__('Start date'), render_value(Qubit::renderDate($grantedRight->startDate))) ?>
        <?php echo render_show(__('End date'), render_value(Qubit::renderDate($grantedRight->endDate))) ?>
        <?php echo render_show(__('Notes'), render_value($grantedRight->notes)) ?>
      <?php endforeach; ?>
    </blockquote>
  </div>
</div>
