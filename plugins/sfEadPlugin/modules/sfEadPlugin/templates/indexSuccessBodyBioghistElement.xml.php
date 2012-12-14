  <?php if (0 < count($creators = $$resourceVar->getCreators())): ?>
  <bioghist encodinganalog="3.2.2">
    <chronlist>
      <?php foreach($$resourceVar->getDates(array('type_id' => QubitTerm::CREATION_ID)) as $date): ?>
      <?php $creator = QubitActor::getById($date->actorId); ?>
      <chronitem>
        <?php echo $ead->renderEadDateFromEvent('creation', $date) ?>
        <eventgrp>
          <event>
            <?php if ($value = $creator->getHistory(array('cultureFallback' => true))): ?>
            <note><?php echo esc_specialchars($value) ?></note>
            <?php endif; ?>
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
