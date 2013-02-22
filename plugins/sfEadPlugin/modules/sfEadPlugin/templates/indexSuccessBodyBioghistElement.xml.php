  <?php
  $creators = $$resourceVar->getCreators();
  $events = $$resourceVar->getActorEvents(array('eventTypeId' => QubitTerm::CREATION_ID));
  if (0 < count($creators)): ?>
  <bioghist encodinganalog="3.2.2">
    <chronlist>
      <?php foreach($events as $date): ?>
      <?php $creator = QubitActor::getById($date->actorId); ?>
      <chronitem>
        <?php echo $ead->renderEadDateFromEvent('creation', $date) ?>
        <eventgrp>
          <event>
            <?php if ($value = $date->getDescription(array('cultureFallback' => true))): ?>
            <note type="eventNote"><p><?php echo esc_specialchars($value) ?></p></note>
            <?php endif; ?>
            <?php if ($value = $creator->getHistory(array('cultureFallback' => true))): ?>
            <note><p><?php echo esc_specialchars($value) ?></p></note>
            <?php endif; ?>
            <origination encodinganalog="3.2.1">
              <?php if ($type = $creator->getEntityTypeId()): ?>
              <?php if ($type == QubitTerm::PERSON_ID): ?>
              <persname source="<?php echo esc_specialchars($creator->datesOfExistence) ?>"><?php echo esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true))) ?></persname>
              <?php endif; ?>
              <?php if ($type == QubitTerm::FAMILY_ID): ?>
              <famname source="<?php echo esc_specialchars($creator->datesOfExistence) ?>"><?php echo esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true))) ?></famname>
              <?php endif; ?>
              <?php if ($type == QubitTerm::CORPORATE_BODY_ID): ?>
              <corpname source="<?php echo esc_specialchars($creator->datesOfExistence) ?>"><?php echo esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true))) ?></corpname>
              <?php endif; ?>
              <?php else: ?>
              <name source="<?php echo esc_specialchars($creator->datesOfExistence) ?>"><?php echo esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true))) ?></name>
              <?php endif; ?>
            </origination>
          </event>
        </eventgrp>
      </chronitem>
      <?php endforeach; ?>
    </chronlist>
  </bioghist>
  <?php endif; ?>
