<?php if (0 < count($creators = $$resourceVar->getCreators())): ?>
    <bioghist encodinganalog="3.2.2">
      <chronlist>
<?php foreach($$resourceVar->getDates(array('type_id' => QubitTerm::CREATION_ID)) as $date): ?>
<?php $creator = QubitActor::getById($date->actorId); ?>
<?php if ($value = $creator->getHistory(array('cultureFallback' => true))): ?>
        <p><?php echo esc_specialchars($value) ?></p>
<?php endif; ?>
        <chronitem>
          <date type="creation"><?php echo $date->date; ?></date>
          <date type="creation_start"><?php echo $date->startDate; ?></date>
          <date type="creation_end"><?php echo $date->endDate; ?></date>
          <eventgrp>
            <event>
              <origination encodinganalog="3.2.1">
<?php if ($type = $creator->getEntityTypeId()): ?>
  <?php if ($type == QubitTerm::PERSON_ID): ?>
      <persname><?php echo esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true))) ?></persname>
  <?php endif; ?>
  <?php if ($type == QubitTerm::FAMILY_ID): ?>
      <famname><?php echo esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true))) ?></famname>
  <?php endif; ?>
  <?php if ($type == QubitTerm::CORPORATE_BODY_ID): ?>
      <corpname><?php echo esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true))) ?></corpname>
  <?php endif; ?>
<?php else: ?>
      <name><?php echo esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true))) ?></name>
<?php endif; ?>
              </origination>
            </event>
          </eventgrp>
        </chronitem>
<?php endforeach; ?>
      </chronlist>
    </bioghist>
<?php endif; ?>
