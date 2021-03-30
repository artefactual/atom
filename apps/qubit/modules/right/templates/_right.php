<div class="field">
  <h3><?php echo __('Related right'); ?></h3>
  <?php if (QubitAcl::check($relatedObject, 'update')) { ?>
    <a href="<?php echo url_for(['module' => 'right', 'action' => 'edit', 'slug' => $resource->slug]); ?>">&nbsp;Edit</a> |
    <a href="<?php echo url_for(['module' => 'right', 'action' => 'delete', 'slug' => $resource->slug]); ?>">Delete</a>
  <?php } ?>

  <div>
    <?php if (isset($inherit)) { ?>
      <?php echo link_to(render_title($inherit), [$inherit, 'module' => 'informationobject'], ['title' => __('Inherited from %1%', ['%1%' => $inherit])]); ?>
    <?php } ?>

    <?php echo render_show(__('Basis'), render_value_inline($resource->basis)); ?>

    <?php echo render_show(__('Start date'), render_value_inline(Qubit::renderDate($resource->startDate))); ?>

    <?php echo render_show(__('End date'), render_value_inline(Qubit::renderDate($resource->endDate))); ?>

    <?php echo render_show(__('Documentation Identifier Type'), render_value_inline($resource->identifierType)); ?>

    <?php echo render_show(__('Documentation Identifier Value'), render_value_inline($resource->identifierValue)); ?>

    <?php echo render_show(__('Documentation Identifier Role'), render_value_inline($resource->identifierRole)); ?>

    <?php if (isset($resource->rightsHolder)) { ?>
      <?php echo render_show(__('Rights holder'), link_to(render_value_inline($resource->rightsHolder), [$resource->rightsHolder, 'module' => 'rightsholder'])); ?>
    <?php } ?>

    <?php echo render_show(__('Rights note(s)'), render_value_inline($resource->getRightsNote(['cultureFallback' => true]))); ?>

    <?php if (QubitTerm::RIGHT_BASIS_COPYRIGHT_ID == $resource->basisId) { ?>

      <?php echo render_show(__('Copyright status'), render_value_inline($resource->copyrightStatus)); ?>

      <?php echo render_show(__('Copyright status determination date'), render_value_inline($resource->copyrightStatusDate)); ?>

      <?php echo render_show(__('Copyright jurisdiction'), render_value_inline($resource->copyrightJurisdiction)); ?>

      <?php echo render_show(__('Copyright note'), render_value_inline($resource->getCopyrightNote(['cultureFallback' => true]))); ?>

    <?php } elseif (QubitTerm::RIGHT_BASIS_LICENSE_ID == $resource->basisId) { ?>

      <?php echo render_show(__('License identifier'), render_value_inline($resource->getIdentifierValue(['cultureFallback' => true]))); ?>

      <?php echo render_show(__('License terms'), render_value_inline($resource->getLicenseTerms(['cultureFallback' => true]))); ?>

      <?php echo render_show(__('License note'), render_value_inline($resource->getLicenseNote(['cultureFallback' => true]))); ?>

    <?php } elseif (QubitTerm::RIGHT_BASIS_STATUTE_ID == $resource->basisId) { ?>

      <?php echo render_show(__('Statute jurisdiction'), render_value_inline($resource->getStatuteJurisdiction(['cultureFallback' => true]))); ?>

      <?php if (null !== $statuteCitation = $resource->statuteCitation) { ?>
        <?php echo render_show(__('Statute citation'), render_value_inline($statuteCitation->getName(['cultureFallback' => true]))); ?>
      <?php } ?>

      <?php echo render_show(__('Statute determination date'), render_value_inline($resource->statuteDeterminationDate)); ?>

      <?php echo render_show(__('Statute note'), render_value_inline($resource->getStatuteNote(['cultureFallback' => true]))); ?>

    <?php } ?>

    <blockquote>
      <?php foreach ($resource->grantedRights as $grantedRight) { ?>
        <hr />
        <?php echo render_show(__('Act'), render_value_inline($grantedRight->act)); ?>
        <?php echo render_show(__('Restriction'), render_value_inline(QubitGrantedRight::getRestrictionString($grantedRight->restriction))); ?>
        <?php echo render_show(__('Start date'), render_value_inline(Qubit::renderDate($grantedRight->startDate))); ?>
        <?php echo render_show(__('End date'), render_value_inline(Qubit::renderDate($grantedRight->endDate))); ?>
        <?php echo render_show(__('Notes'), render_value_inline($grantedRight->notes)); ?>
      <?php } ?>
    </blockquote>
  </div>
</div>
