<div class="field">
  <h3><?php echo __('Related right') ?></h3>
  <div>

    <a data-target="#basisModal<?php echo $resource->id ?>" role="button" class="btn" data-toggle="modal">Edit</a>

    <div class="modal fade hide" id="basisModal<?php echo $resource->id ?>" tabindex="-1" 
      role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" 
      data-remote="/right/modal?id=<?php echo $resource->id ?>">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Set/Modify Rights</h3>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button class="btn btn-primary">Save changes</button>
      </div>
    </div>
    
    <?php if (isset($inherit)): ?>
      <?php echo link_to(render_title($inherit), array($inherit, 'module' => 'informationobject'), array('title' => __('Inherited from %1%', array('%1%' => $inherit)))) ?>
    <?php endif; ?>

    <?php echo render_show(__('Basis'), render_value($resource->basis)) ?>

    <?php echo render_show(__('Start date'), render_value(Qubit::renderDate($resource->startDate))) ?>

    <?php echo render_show(__('End date'), render_value(Qubit::renderDate($resource->endDate))) ?>

    <?php if (isset($resource->rightsHolder)): ?>
      <?php echo render_show(__('Rights holder'), link_to(render_value($resource->rightsHolder), array($resource->rightsHolder, 'module' => 'rightsholder'))) ?>
    <?php endif; ?>

    <?php echo render_show(__('Rights note(s)'), render_value($resource->getRightsNote(array('cultureFallback' => true)))) ?>

    <?php if (QubitTerm::RIGHT_BASIS_COPYRIGHT_ID == $resource->basisId): ?>

      <?php echo render_show(__('Copyright status'), render_value($resource->copyrightStatus)) ?>

      <?php echo render_show(__('Copyright status date'), render_value($resource->copyrightStatusDate)) ?>

      <?php echo render_show(__('Copyright jurisdiction'), render_value(format_country($resource->copyrightJurisdiction))) ?>

      <?php echo render_show(__('Copyright note'), render_value($resource->getCopyrightNote(array('cultureFallback' => true)))) ?>

    <?php elseif (QubitTerm::RIGHT_BASIS_LICENSE_ID == $resource->basisId): ?>

      <?php echo render_show(__('License identifier'), render_value($resource->getLicenseIdentifier(array('cultureFallback' => true)))) ?>

      <?php echo render_show(__('License terms'), render_value($resource->getLicenseTerms(array('cultureFallback' => true)))) ?>

      <?php echo render_show(__('License note'), render_value($resource->getLicenseNote(array('cultureFallback' => true)))) ?>

    <?php elseif (QubitTerm::RIGHT_BASIS_STATUTE_ID == $resource->basisId): ?>

      <?php echo render_show(__('Statute jurisdiction'), render_value($resource->getStatuteJurisdiction(array('cultureFallback' => true)))) ?>

      <?php echo render_show(__('Statute citation'), render_value($resource->getStatuteCitation(array('cultureFallback' => true)))) ?>

      <?php echo render_show(__('Statute determination date'), render_value($resource->statuteDeterminationDate)) ?>

      <?php echo render_show(__('Statute note'), render_value($resource->getStatuteNote(array('cultureFallback' => true)))) ?>

    <?php endif; ?>

    <?php foreach($resource->grantedRights as $item): ?>
      <div class="field act-description">
        <h3>Act</h3>
        <div>
          <?php echo render_value($item->restriction ? __('Allow') : __('Disallow')) ?> <?php echo render_value($item->act) ?> 
          <i>from</i> <?php echo render_value(Qubit::renderDate($item->startDate)) ?>
          <?php if($item->endDate): ?>
            <i>thru</i>
            <?php echo render_value(Qubit::renderDate($item->endDate)) ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach?>

    <style type="text/css">
      .act-description:hover {
        cursor: pointer;
        background-color:#eee;
      }
    </style>



  </div>
</div>
