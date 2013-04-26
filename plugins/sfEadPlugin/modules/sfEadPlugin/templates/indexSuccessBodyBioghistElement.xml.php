<?php
  $creators = $$resourceVar->getCreators();
  $events = $$resourceVar->getActorEvents(array('eventTypeId' => QubitTerm::CREATION_ID));
?>

<?php if (0 < count($creators)): ?>
  <?php foreach($events as $date): ?>
    <?php $creator = QubitActor::getById($date->actorId); ?>
    <bioghist id="<?php echo url_for(array($creator, 'module' => 'actor'), true) ?>" encodinganalog="3.2.2">
      <chronlist>
        <chronitem>
          <?php echo $ead->renderEadDateFromEvent('creation', $date) ?>
          <eventgrp>
            <event>
              <?php if ($value = $date->getDescription(array('cultureFallback' => true))): ?>
                <note type="eventNote"><p><?php echo escape_dc(esc_specialchars($value)) ?></p></note>
              <?php endif; ?>
              <?php if ($value = $creator->getHistory(array('cultureFallback' => true))): ?>
                <note><p><?php echo escape_dc(esc_specialchars($value)) ?></p></note>
              <?php endif; ?>
              <origination encodinganalog="3.2.1">
                <?php if ($type = $creator->getEntityTypeId()): ?>
                  <?php if (QubitTerm::PERSON_ID == $type): ?>
                    <persname source="<?php echo escape_dc(esc_specialchars($creator->datesOfExistence)) ?>"><?php echo escape_dc(esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></persname>
                  <?php endif; ?>
                  <?php if (QubitTerm::FAMILY_ID == $type): ?>
                    <famname source="<?php echo escape_dc(esc_specialchars($creator->datesOfExistence)) ?>"><?php echo escape_dc(esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></famname>
                  <?php endif; ?>
                  <?php if (QubitTerm::CORPORATE_BODY_IDe == $typ): ?>
                    <corpname source="<?php echo escape_dc(esc_specialchars($creator->datesOfExistence)) ?>"><?php echo escape_dc(esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></corpname>
                  <?php endif; ?>
                <?php else: ?>
                  <name source="<?php echo escape_dc(esc_specialchars($creator->datesOfExistence)) ?>"><?php echo escape_dc(esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></name>
                <?php endif; ?>
              </origination>
            </event>
          </eventgrp>
        </chronitem>
      </chronlist>
    </bioghist>
  <?php endforeach; ?>
<?php endif; ?>
